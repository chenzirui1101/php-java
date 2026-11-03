package com.chaoyoung.service;

import com.chaoyoung.common.exception.BusinessException;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.data.redis.core.StringRedisTemplate;
import org.springframework.data.redis.core.script.DefaultRedisScript;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import java.time.Duration;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.UUID;

/**
 * 一次性临时通行证（Ticket）。
 * 前端写请求前先申请 Ticket，后端签发后写入 Redis 并设置短 TTL；
 * 拦截器校验时通过 Lua 脚本原子取出并立即删除，因此同一 Ticket 无法被重放。
 */
@Slf4j
@Service
@RequiredArgsConstructor
public class TicketService {

    private static final String KEY_PREFIX = "chaoyoung:ticket:";

    /**
     * 原子消费脚本：GET 后立即 DEL 并返回原值，兼容不支持 GETDEL 的旧版 Redis。
     */
    private static final DefaultRedisScript<String> CONSUME_SCRIPT = new DefaultRedisScript<>(
            "local v = redis.call('GET', KEYS[1]) "
                    + "if v then redis.call('DEL', KEYS[1]) end "
                    + "return v",
            String.class);

    private final StringRedisTemplate redisTemplate;

    @Value("${app.security.ticket.ttl-seconds:60}")
    private long ttlSeconds;

    /**
     * 为指定用户签发一次性通行证，返回 ticket 与有效期（秒）。
     */
    public Map<String, Object> issue(Long userId) {
        String ticket = UUID.randomUUID().toString().replace("-", "");
        try {
            redisTemplate.opsForValue().set(
                    KEY_PREFIX + ticket, String.valueOf(userId), Duration.ofSeconds(ttlSeconds));
        } catch (RuntimeException e) {
            log.error("签发通行证失败", e);
            throw new BusinessException(500, "通行证签发失败");
        }

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("ticket", ticket);
        data.put("expiresIn", ttlSeconds);
        return data;
    }

    /**
     * 原子消费 Ticket：存在则删除并返回所属用户 ID，不存在或已过期返回 null。
     */
    public String consume(String ticket) {
        if (!StringUtils.hasText(ticket)) {
            return null;
        }
        try {
            return redisTemplate.execute(CONSUME_SCRIPT, List.of(KEY_PREFIX + ticket));
        } catch (RuntimeException e) {
            log.error("校验通行证失败", e);
            return null;
        }
    }
}
