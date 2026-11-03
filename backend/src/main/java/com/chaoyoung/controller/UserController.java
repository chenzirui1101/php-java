package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Follow;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.FollowMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.time.LocalDateTime;
import java.util.LinkedHashMap;
import java.util.Map;

/**
 * 用户公开资料与关注接口，对齐 Laravel ForumController::userProfile / userFollow。
 */
@RestController
@RequestMapping("/api/user")
@RequiredArgsConstructor
public class UserController {

    private final UserMapper userMapper;
    private final FollowMapper followMapper;

    @GetMapping("/{id}/profile")
    public Result<Map<String, Object>> profile(@PathVariable Long id) {
        User user = userMapper.selectById(id);
        if (user == null) {
            return Result.fail(404, "用户不存在");
        }

        Long currentUserId = SecurityUtils.currentUserId();
        boolean isFollowing = currentUserId != null && followMapper.selectCount(
                new LambdaQueryWrapper<Follow>()
                        .eq(Follow::getFollowerId, currentUserId)
                        .eq(Follow::getFollowedId, id)) > 0;

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("name", user.getUsername());
        data.put("bio", user.getBio() == null ? "" : user.getBio());
        data.put("avatar", user.getAvatar() == null ? "" : user.getAvatar());
        data.put("topics", user.getTopicsCount() == null ? 0 : user.getTopicsCount());
        // user_stats 表尚未在当前 Java 库中建立，阅读数暂返回 0
        data.put("reads", 0);
        data.put("fans", followMapper.selectCount(
                new LambdaQueryWrapper<Follow>().eq(Follow::getFollowedId, id)));
        data.put("isFollowing", isFollowing);
        return Result.ok("success", data);
    }

    @PostMapping("/{id}/follow")
    @Transactional(rollbackFor = Exception.class)
    public Result<Map<String, Object>> toggleFollow(@PathVariable Long id) {
        Long currentUserId = SecurityUtils.currentUserId();
        if (currentUserId == null) {
            throw new BusinessException(401, "请先登录");
        }
        if (currentUserId.equals(id)) {
            throw new BusinessException(400, "不能关注自己");
        }
        if (userMapper.selectById(id) == null) {
            throw new BusinessException(404, "用户不存在");
        }

        LambdaQueryWrapper<Follow> wrapper = new LambdaQueryWrapper<Follow>()
                .eq(Follow::getFollowerId, currentUserId)
                .eq(Follow::getFollowedId, id);

        boolean following;
        if (followMapper.selectCount(wrapper) > 0) {
            followMapper.delete(wrapper);
            following = false;
        } else {
            Follow follow = new Follow();
            follow.setFollowerId(currentUserId);
            follow.setFollowedId(id);
            follow.setCreatedAt(LocalDateTime.now());
            follow.setUpdatedAt(LocalDateTime.now());
            followMapper.insert(follow);
            following = true;
        }

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("isFollowing", following);
        return Result.ok(following ? "已关注" : "已取消关注", data);
    }
}
