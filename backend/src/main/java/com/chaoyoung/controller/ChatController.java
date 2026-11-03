package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Conversation;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.ConversationMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.time.format.DateTimeFormatter;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 会话列表，逻辑对齐 Laravel ChatController@getConversations：
 * 查询当前用户作为买家或卖家的会话，按最后消息时间倒序，并补充 other_user 字段。
 */
@RestController
@RequestMapping("/api/chat")
@RequiredArgsConstructor
public class ChatController {

    private static final DateTimeFormatter DATE_TIME = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");

    private final ConversationMapper conversationMapper;
    private final UserMapper userMapper;

    @GetMapping("/conversations")
    public Result<List<Map<String, Object>>> conversations() {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "未登录或登录已过期");
        }

        LambdaQueryWrapper<Conversation> wrapper = new LambdaQueryWrapper<Conversation>()
                .and(w -> w.eq(Conversation::getUserId, userId).or().eq(Conversation::getSellerId, userId))
                .orderByDesc(Conversation::getLastMessageTime);

        List<Conversation> conversations = conversationMapper.selectList(wrapper);
        Map<Long, User> users = loadUsers(conversations);

        List<Map<String, Object>> data = conversations.stream()
                .map(item -> toItem(item, userId, users))
                .collect(Collectors.toList());
        return Result.ok("获取会话列表成功", data);
    }

    private Map<Long, User> loadUsers(List<Conversation> conversations) {
        Set<Long> userIds = conversations.stream()
                .flatMap(item -> java.util.stream.Stream.of(item.getUserId(), item.getSellerId()))
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());
        if (userIds.isEmpty()) {
            return Map.of();
        }
        return userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity()));
    }

    private Map<String, Object> toItem(Conversation item, Long currentUserId, Map<Long, User> users) {
        Map<String, Object> row = new LinkedHashMap<>();
        row.put("id", item.getId());
        row.put("product_id", item.getProductId());
        row.put("user_id", item.getUserId());
        row.put("seller_id", item.getSellerId());
        row.put("last_message", item.getLastMessage());
        row.put("last_message_time",
                item.getLastMessageTime() == null ? null : item.getLastMessageTime().format(DATE_TIME));
        row.put("unread_count", item.getUnreadCount());
        row.put("resource_type", item.getResourceType());
        row.put("resource_id", item.getResourceId());
        row.put("created_at", item.getCreatedAt() == null ? null : item.getCreatedAt().format(DATE_TIME));

        if (item.getProductId() != null) {
            Map<String, Object> product = new LinkedHashMap<>();
            product.put("id", item.getProductId());
            row.put("product", product);
        }

        Long otherUserId = currentUserId.equals(item.getUserId()) ? item.getSellerId() : item.getUserId();
        User otherUser = users.get(otherUserId);
        if (otherUser != null) {
            Map<String, Object> other = new LinkedHashMap<>();
            other.put("id", otherUser.getId());
            // Laravel 旧接口这里返回的是 name 字段
            other.put("username", otherUser.getName());
            other.put("avatar", otherUser.getAvatar());
            row.put("other_user", other);
        }
        return row;
    }
}
