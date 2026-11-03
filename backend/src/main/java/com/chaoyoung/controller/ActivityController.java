package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Activity;
import com.chaoyoung.mapper.ActivityMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * 校园活动列表，逻辑对齐 Laravel ActivityController@index：
 * 仅 approved、keyword/category_id 过滤、按创建时间倒序、分页（默认每页 12）。
 */
@RestController
@RequestMapping("/api/activities")
@RequiredArgsConstructor
public class ActivityController {

    private final ActivityMapper activityMapper;

    @GetMapping
    public Result<Map<String, Object>> index(
            @RequestParam(defaultValue = "1") long page,
            @RequestParam(defaultValue = "12") long pageSize,
            @RequestParam(name = "category_id", required = false) String categoryId,
            @RequestParam(required = false) String keyword) {

        LambdaQueryWrapper<Activity> wrapper = new LambdaQueryWrapper<>();
        wrapper.eq(Activity::getModerationStatus, "approved");
        if (StringUtils.hasText(keyword)) {
            wrapper.like(Activity::getTitle, keyword);
        }
        if (StringUtils.hasText(categoryId) && !"all".equals(categoryId) && !"0".equals(categoryId)) {
            wrapper.eq(Activity::getCategoryId, Long.valueOf(categoryId));
        }
        wrapper.orderByDesc(Activity::getCreatedAt);

        Page<Activity> pageResult = activityMapper.selectPage(new Page<>(page, pageSize), wrapper);

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("current_page", pageResult.getCurrent());
        data.put("last_page", pageResult.getPages());
        data.put("per_page", pageResult.getSize());
        data.put("total", pageResult.getTotal());
        data.put("data", pageResult.getRecords());
        return Result.ok("success", data);
    }

    @PostMapping
    public Result<Activity> store(@RequestBody(required = false) Map<String, Object> body) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        if (body == null) {
            throw new BusinessException(422, "请求参数不能为空");
        }

        String title = asString(body.get("title"));
        String poster = asString(body.get("poster"));
        Long categoryId = toLong(body.get("category_id"));
        if (!StringUtils.hasText(title)) {
            throw new BusinessException(422, "活动标题不能为空");
        }
        if (title.length() > 255) {
            throw new BusinessException(422, "活动标题不能超过255个字符");
        }
        if (!StringUtils.hasText(poster)) {
            throw new BusinessException(422, "请上传活动海报");
        }
        if (categoryId == null) {
            throw new BusinessException(422, "请选择活动分类");
        }

        LocalDateTime startTime = parseDateTime(asString(body.get("start_time")), "开始时间");
        LocalDateTime endTime = parseDateTime(asString(body.get("end_time")), "结束时间");
        if (startTime == null || endTime == null) {
            throw new BusinessException(422, "开始时间和结束时间不能为空");
        }
        if (!endTime.isAfter(startTime)) {
            throw new BusinessException(422, "结束时间必须晚于开始时间");
        }

        Activity activity = new Activity();
        activity.setTitle(title);
        activity.setPoster(poster);
        activity.setStartTime(startTime);
        activity.setEndTime(endTime);
        activity.setDescription(asString(body.get("description")));
        activity.setCategoryId(categoryId);
        activity.setUserId(userId);
        activity.setModerationStatus("pending");
        activity.setCreatedAt(LocalDateTime.now());
        activity.setUpdatedAt(LocalDateTime.now());
        activityMapper.insert(activity);
        return Result.ok("发布成功，请等待管理员审核", activity);
    }

    private LocalDateTime parseDateTime(String value, String fieldName) {
        if (!StringUtils.hasText(value)) {
            return null;
        }
        for (DateTimeFormatter formatter : List.of(
                DateTimeFormatter.ISO_LOCAL_DATE_TIME,
                DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss"),
                DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm"))) {
            try {
                return LocalDateTime.parse(value.trim(), formatter);
            } catch (DateTimeParseException ignored) {
                // 尝试下一种格式
            }
        }
        throw new BusinessException(422, fieldName + "格式不正确");
    }

    private String asString(Object value) {
        return value == null ? null : String.valueOf(value);
    }

    private Long toLong(Object value) {
        if (value instanceof Number number) {
            return number.longValue();
        }
        if (value instanceof String text && StringUtils.hasText(text)) {
            try {
                return Long.valueOf(text);
            } catch (NumberFormatException ignored) {
                return null;
            }
        }
        return null;
    }
}
