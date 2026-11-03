package com.chaoyoung.aspect;

import com.chaoyoung.annotation.ContentModeration;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.service.ContentModerationService;
import lombok.RequiredArgsConstructor;
import org.aspectj.lang.ProceedingJoinPoint;
import org.aspectj.lang.annotation.Around;
import org.aspectj.lang.annotation.Aspect;
import org.springframework.stereotype.Component;

import java.lang.reflect.Method;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Map;

/**
 * 内容审核切面：提取 @ContentModeration 声明的字段，违规时抛出 422。
 */
@Aspect
@Component
@RequiredArgsConstructor
public class ContentModerationAspect {

    private final ContentModerationService contentModerationService;

    @Around("@annotation(contentModeration)")
    public Object around(ProceedingJoinPoint joinPoint, ContentModeration contentModeration) throws Throwable {
        Map<String, String> texts = extractTexts(joinPoint.getArgs(), contentModeration.fields());

        List<String> reasons = new ArrayList<>();
        texts.forEach((field, text) -> {
            List<String> violations = contentModerationService.check(text, contentModeration.type());
            for (String violation : violations) {
                reasons.add(field + "：" + violation);
            }
        });

        if (!reasons.isEmpty()) {
            Map<String, Object> errors = new LinkedHashMap<>();
            errors.put("content", new ArrayList<>(new LinkedHashSet<>(reasons)));
            throw new BusinessException(422, "内容审核未通过", errors);
        }
        return joinPoint.proceed();
    }

    private Map<String, String> extractTexts(Object[] args, String[] fields) {
        Map<String, String> texts = new LinkedHashMap<>();
        for (Object arg : args) {
            if (arg == null) {
                continue;
            }
            if (arg instanceof Map<?, ?> map) {
                for (String field : fields) {
                    Object value = map.get(field);
                    if (value instanceof String text && !text.isBlank()) {
                        texts.putIfAbsent(field, text);
                    }
                }
                continue;
            }
            for (String field : fields) {
                Object value = readProperty(arg, field);
                if (value instanceof String text && !text.isBlank()) {
                    texts.putIfAbsent(field, text);
                }
            }
        }
        return texts;
    }

    private Object readProperty(Object target, String field) {
        String getter = "get" + Character.toUpperCase(field.charAt(0)) + field.substring(1);
        try {
            Method method = target.getClass().getMethod(getter);
            return method.invoke(target);
        } catch (NoSuchMethodException e) {
            return null;
        } catch (ReflectiveOperationException e) {
            throw new IllegalStateException("读取审核字段失败: " + field, e);
        }
    }
}
