package com.chaoyoung.common.exception;

import lombok.Getter;

import java.util.Map;

/**
 * 业务异常。code 同时作为 HTTP 状态码返回，errors 用于 422 校验明细。
 */
@Getter
public class BusinessException extends RuntimeException {

    private final int code;
    private final Map<String, Object> errors;

    public BusinessException(int code, String message) {
        this(code, message, null);
    }

    public BusinessException(int code, String message, Map<String, Object> errors) {
        super(message);
        this.code = code;
        this.errors = errors;
    }
}
