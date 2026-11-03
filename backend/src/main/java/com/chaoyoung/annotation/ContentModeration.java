package com.chaoyoung.annotation;

import java.lang.annotation.Documented;
import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;

/**
 * 复刻 Laravel 的 content.moderation 中间件。
 * 标注在 Controller 方法上，由 ContentModerationAspect 提取字段并做敏感内容审核。
 */
@Documented
@Target(ElementType.METHOD)
@Retention(RetentionPolicy.RUNTIME)
public @interface ContentModeration {

    /** 审核类型：product / forum / comment / lostfound 等 */
    String type() default "general";

    /** 需要审核的字段名（DTO 的 getter 或 Map 的 key） */
    String[] fields() default {"title", "content", "description"};
}
