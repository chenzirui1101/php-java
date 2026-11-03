package com.chaoyoung.interceptor;

import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.service.TicketService;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;
import org.springframework.util.StringUtils;
import org.springframework.web.servlet.HandlerInterceptor;

/**
 * 一次性通行证拦截器（防重放）。
 * 读请求（GET/HEAD/OPTIONS）直接放行；
 * 写请求（POST/PUT/PATCH/DELETE）必须携带 X-Ticket，
 * 后端在 Redis 中原子取出并删除该 Ticket，保证只能使用一次。
 * Ticket 由 GET /api/auth/ticket 在 JWT 登录态下签发，有效期默认 60 秒。
 */
@Component
@RequiredArgsConstructor
public class AntiTamperInterceptor implements HandlerInterceptor {

    private final TicketService ticketService;

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) {
        String method = request.getMethod();
        if ("GET".equalsIgnoreCase(method) || "HEAD".equalsIgnoreCase(method)
                || "OPTIONS".equalsIgnoreCase(method)) {
            return true;
        }

        String ticket = request.getHeader("X-Ticket");
        if (!StringUtils.hasText(ticket)) {
            throw new BusinessException(401, "缺少通行证 X-Ticket");
        }
        if (ticketService.consume(ticket) == null) {
            throw new BusinessException(401, "通行证无效或已过期");
        }
        return true;
    }
}
