package com.chaoyoung.common;

import lombok.Data;

import java.util.Map;

/**
 * 统一 API 返回结构，与 Laravel 旧接口保持 code/msg/data/errors 约定。
 */
@Data
public class Result<T> {

    private int code;
    private String msg;
    private T data;
    private Long total;
    private Map<String, Object> errors;

    public static <T> Result<T> ok() {
        return ok(null);
    }

    public static <T> Result<T> ok(T data) {
        Result<T> result = new Result<>();
        result.setCode(200);
        result.setMsg("success");
        result.setData(data);
        return result;
    }

    public static <T> Result<T> ok(String msg, T data) {
        Result<T> result = ok(data);
        result.setMsg(msg);
        return result;
    }

    public static <T> Result<T> fail(int code, String msg) {
        Result<T> result = new Result<>();
        result.setCode(code);
        result.setMsg(msg);
        return result;
    }

    public static <T> Result<T> fail(int code, String msg, Map<String, Object> errors) {
        Result<T> result = fail(code, msg);
        result.setErrors(errors);
        return result;
    }
}
