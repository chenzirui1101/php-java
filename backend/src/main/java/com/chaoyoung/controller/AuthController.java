package com.chaoyoung.controller;

import com.chaoyoung.common.Result;
import com.chaoyoung.dto.LoginRequest;
import com.chaoyoung.dto.RegisterRequest;
import com.chaoyoung.dto.UpdateProfileRequest;
import com.chaoyoung.entity.User;
import com.chaoyoung.security.SecurityUtils;
import com.chaoyoung.service.AuthService;
import com.chaoyoung.service.TicketService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.LinkedHashMap;
import java.util.Map;
import java.util.concurrent.ThreadLocalRandom;

/**
 * 认证接口，路径与 Laravel routes/api.php 一致：/api/auth/*
 */
@RestController
@RequestMapping("/api/auth")
@RequiredArgsConstructor
public class AuthController {

    private final AuthService authService;

    private final TicketService ticketService;

    @PostMapping("/login")
    public Result<Map<String, Object>> login(@Valid @RequestBody LoginRequest request) {
        return Result.ok("登录成功", authService.login(request));
    }

    @PostMapping("/register")
    public Result<Map<String, Object>> register(@Valid @RequestBody RegisterRequest request) {
        return Result.ok("注册成功", authService.register(request));
    }

    @GetMapping("/me")
    public Result<Map<String, Object>> me() {
        User user = authService.requireUser(requireCurrentUserId());
        return Result.ok("获取成功", authService.userPayload(user));
    }

    @GetMapping("/check-token")
    public Result<Map<String, Object>> checkToken() {
        User user = authService.requireUser(requireCurrentUserId());
        return Result.ok("token有效", authService.userPayload(user));
    }

    @PostMapping("/update")
    public Result<Map<String, Object>> update(@Valid @RequestBody UpdateProfileRequest request) {
        return Result.ok("更新成功", authService.updateProfile(requireCurrentUserId(), request));
    }

    /**
     * 签发一次性写请求通行证，有效期 60 秒，使用后立即失效。
     */
    @GetMapping("/ticket")
    public Result<Map<String, Object>> ticket() {
        return Result.ok("获取成功", ticketService.issue(requireCurrentUserId()));
    }

    @GetMapping("/captcha")
    public Result<Map<String, Object>> captcha() {
        int num1 = ThreadLocalRandom.current().nextInt(1, 21);
        int num2 = ThreadLocalRandom.current().nextInt(1, 21);
        boolean plus = ThreadLocalRandom.current().nextBoolean();
        if (!plus && num1 < num2) {
            int temp = num1;
            num1 = num2;
            num2 = temp;
        }
        int answer = plus ? num1 + num2 : num1 - num2;
        String text = num1 + (plus ? " + " : " - ") + num2 + " = ?";

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("captcha", String.valueOf(answer));
        data.put("image", text);
        return Result.ok("获取成功", data);
    }

    private Long requireCurrentUserId() {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new com.chaoyoung.common.exception.BusinessException(401, "未登录或登录已过期");
        }
        return userId;
    }
}
