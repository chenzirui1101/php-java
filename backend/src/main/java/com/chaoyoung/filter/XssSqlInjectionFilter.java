package com.chaoyoung.filter;

import com.chaoyoung.common.util.SanitizerUtils;
import com.fasterxml.jackson.databind.JsonNode;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.node.ObjectNode;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.core.annotation.Order;
import org.springframework.http.MediaType;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.util.ArrayList;
import java.util.List;

/**
 * 全局请求清洗过滤器：拦截并清洗 Query/Form 参数以及 JSON 请求体中的 XSS/SQL 注入内容。
 */
@Slf4j
@Component
@Order(2)
@RequiredArgsConstructor
public class XssSqlInjectionFilter extends OncePerRequestFilter {

    private final ObjectMapper objectMapper;

    @Override
    protected void doFilterInternal(HttpServletRequest request, HttpServletResponse response, FilterChain filterChain)
            throws ServletException, IOException {
        if ("OPTIONS".equalsIgnoreCase(request.getMethod())) {
            filterChain.doFilter(request, response);
            return;
        }

        String contentType = request.getContentType();
        if (contentType != null && contentType.toLowerCase().startsWith(MediaType.APPLICATION_JSON_VALUE)) {
            String rawBody = new String(request.getInputStream().readAllBytes(), StandardCharsets.UTF_8);
            String sanitized = rawBody.isBlank() ? rawBody : sanitizeJson(rawBody);
            filterChain.doFilter(new SanitizedRequestWrapper(request, sanitized.getBytes(StandardCharsets.UTF_8)), response);
            return;
        }

        filterChain.doFilter(new SanitizedRequestWrapper(request, null), response);
    }

    private String sanitizeJson(String rawBody) {
        try {
            JsonNode root = objectMapper.readTree(rawBody);
            sanitizeNode(root);
            return objectMapper.writeValueAsString(root);
        } catch (Exception e) {
            log.warn("JSON 请求体清洗失败，保持原始内容: {}", e.getMessage());
            return rawBody;
        }
    }

    private void sanitizeNode(JsonNode node) {
        if (node == null) {
            return;
        }
        if (node.isObject()) {
            ObjectNode objectNode = (ObjectNode) node;
            List<String> fieldNames = new ArrayList<>();
            objectNode.fieldNames().forEachRemaining(fieldNames::add);
            for (String field : fieldNames) {
                JsonNode child = objectNode.get(field);
                if (child.isTextual()) {
                    objectNode.put(field, SanitizerUtils.sanitize(field, child.asText()));
                } else {
                    sanitizeNode(child);
                }
            }
        } else if (node.isArray()) {
            for (JsonNode child : node) {
                sanitizeNode(child);
            }
        }
    }
}
