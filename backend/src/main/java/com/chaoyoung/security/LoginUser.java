package com.chaoyoung.security;

import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.io.Serializable;

/**
 * 放入 SecurityContext 的登录用户，字段与 Laravel 返回的 user 信息保持一致。
 */
@Data
@NoArgsConstructor
@AllArgsConstructor
public class LoginUser implements Serializable {

    private Long id;

    private String username;

    private String role;
}
