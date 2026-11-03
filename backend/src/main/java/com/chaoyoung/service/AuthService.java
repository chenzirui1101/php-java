package com.chaoyoung.service;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.dto.LoginRequest;
import com.chaoyoung.dto.RegisterRequest;
import com.chaoyoung.dto.UpdateProfileRequest;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.JwtUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * 认证业务，逻辑对齐 Laravel AuthController。
 */
@Service
@RequiredArgsConstructor
public class AuthService {

    private final UserMapper userMapper;
    private final PasswordEncoder passwordEncoder;
    private final JwtUtils jwtUtils;

    public Map<String, Object> login(LoginRequest request) {
        User user = userMapper.selectByLogin(request.getUsername());
        if (user == null || !passwordEncoder.matches(request.getPassword(), user.getPassword())) {
            throw new BusinessException(422, "验证失败", Map.of("username", List.of("用户名或密码错误")));
        }
        return buildAuthPayload(user);
    }

    @Transactional(rollbackFor = Exception.class)
    public Map<String, Object> register(RegisterRequest request) {
        Long usernameCount = userMapper.selectCount(
                new LambdaQueryWrapper<User>().eq(User::getUsername, request.getUsername()));
        if (usernameCount != null && usernameCount > 0) {
            throw new BusinessException(422, "验证失败", Map.of("username", List.of("用户名已被使用")));
        }
        Long emailCount = userMapper.selectCount(
                new LambdaQueryWrapper<User>().eq(User::getEmail, request.getEmail()));
        if (emailCount != null && emailCount > 0) {
            throw new BusinessException(422, "验证失败", Map.of("email", List.of("邮箱已被使用")));
        }

        User user = new User();
        user.setUsername(request.getUsername());
        user.setEmail(request.getEmail());
        user.setName(request.getName() == null ? request.getUsername() : request.getName());
        user.setPassword(passwordEncoder.encode(request.getPassword()));
        user.setRole("user");
        user.setTopicsCount(0);
        userMapper.insert(user);
        return buildAuthPayload(user);
    }

    public Map<String, Object> updateProfile(Long userId, UpdateProfileRequest request) {
        User user = requireUser(userId);
        if (request.getName() != null) {
            user.setName(request.getName());
        }
        if (request.getAvatar() != null) {
            user.setAvatar(request.getAvatar());
        }
        userMapper.updateById(user);
        return userPayload(user);
    }

    public User requireUser(Long userId) {
        User user = userMapper.selectById(userId);
        if (user == null) {
            throw new BusinessException(401, "用户不存在或已注销");
        }
        return user;
    }

    public Map<String, Object> buildAuthPayload(User user) {
        Map<String, Object> data = new LinkedHashMap<>();
        data.put("token", jwtUtils.generateToken(user.getId(), user.getUsername(), user.getRole()));
        data.put("user", userPayload(user));
        return data;
    }

    public Map<String, Object> userPayload(User user) {
        Map<String, Object> payload = new LinkedHashMap<>();
        payload.put("id", user.getId());
        payload.put("username", user.getUsername());
        payload.put("email", user.getEmail());
        payload.put("name", user.getName());
        payload.put("avatar", user.getAvatar());
        payload.put("role", user.getRole());
        return payload;
    }
}
