package com.chaoyoung.security;

import org.springframework.security.core.Authentication;
import org.springframework.security.core.context.SecurityContextHolder;

/**
 * 获取当前登录用户的便捷方法，未登录时返回 null。
 */
public final class SecurityUtils {

    private SecurityUtils() {
    }

    public static LoginUser currentUser() {
        Authentication authentication = SecurityContextHolder.getContext().getAuthentication();
        if (authentication != null && authentication.getPrincipal() instanceof LoginUser loginUser) {
            return loginUser;
        }
        return null;
    }

    public static Long currentUserId() {
        LoginUser user = currentUser();
        return user == null ? null : user.getId();
    }

    public static boolean isAdmin() {
        LoginUser user = currentUser();
        return user != null && "admin".equalsIgnoreCase(user.getRole());
    }
}
