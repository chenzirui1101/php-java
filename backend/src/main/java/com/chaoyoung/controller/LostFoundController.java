package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.LostFound;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.LostFoundMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 失物招领列表，逻辑对齐 Laravel LostFoundController@index：
 * keyword/type/status 过滤、仅 approved、按创建时间倒序、每页 12 条。
 */
@RestController
@RequestMapping("/api/lostfound")
@RequiredArgsConstructor
public class LostFoundController {

    private static final DateTimeFormatter DATE_TIME = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");

    private final LostFoundMapper lostFoundMapper;
    private final UserMapper userMapper;

    @GetMapping
    public Result<Map<String, Object>> index(
            @RequestParam(required = false) String keyword,
            @RequestParam(required = false) String type,
            @RequestParam(required = false) String status,
            @RequestParam(defaultValue = "1") long page) {

        LambdaQueryWrapper<LostFound> wrapper = new LambdaQueryWrapper<>();
        if (StringUtils.hasText(keyword)) {
            wrapper.and(w -> w.like(LostFound::getTitle, keyword)
                    .or()
                    .like(LostFound::getDescription, keyword));
        }
        if (StringUtils.hasText(type) && !"all".equals(type)) {
            wrapper.eq(LostFound::getType, type);
        }
        if (StringUtils.hasText(status) && !"all".equals(status)) {
            wrapper.eq(LostFound::getStatus, status);
        }
        wrapper.eq(LostFound::getModerationStatus, "approved");
        wrapper.orderByDesc(LostFound::getCreatedAt);

        Page<LostFound> pageResult = lostFoundMapper.selectPage(new Page<>(page, 12), wrapper);

        Map<Long, User> users = loadUsers(pageResult.getRecords());
        List<Map<String, Object>> list = pageResult.getRecords().stream()
                .map(item -> toListItem(item, users.get(item.getUserId())))
                .collect(Collectors.toList());

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("list", list);
        data.put("total", pageResult.getTotal());
        data.put("pageNum", pageResult.getCurrent());
        data.put("pageSize", pageResult.getSize());
        return Result.ok("success", data);
    }

    @GetMapping("/{id}")
    public Result<Map<String, Object>> show(@PathVariable Long id) {
        LostFound item = lostFoundMapper.selectById(id);
        if (item == null
                || (!"approved".equals(item.getModerationStatus())
                && !"pending".equals(item.getModerationStatus()))) {
            throw new BusinessException(404, "失物招领不存在");
        }
        User user = item.getUserId() == null ? null : userMapper.selectById(item.getUserId());
        return Result.ok("success", toListItem(item, user));
    }

    @PostMapping
    public Result<LostFound> store(@RequestBody(required = false) Map<String, Object> body) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        if (body == null) {
            throw new BusinessException(422, "请求参数不能为空");
        }

        String title = asString(body.get("title"));
        String type = asString(body.get("type"));
        String status = asString(body.get("status"));
        String location = asString(body.get("location"));
        String description = asString(body.get("description"));

        if (!StringUtils.hasText(title)) {
            throw new BusinessException(422, "标题不能为空");
        }
        if (title.length() > 255) {
            throw new BusinessException(422, "标题不能超过255个字符");
        }
        if (!"lost".equals(type) && !"found".equals(type)) {
            throw new BusinessException(422, "类型必须为 lost 或 found");
        }
        if (!"looking".equals(status) && !"found".equals(status)) {
            throw new BusinessException(422, "状态必须为 looking 或 found");
        }
        if (!StringUtils.hasText(location)) {
            throw new BusinessException(422, "地点不能为空");
        }
        if (!StringUtils.hasText(description)) {
            throw new BusinessException(422, "描述不能为空");
        }

        LostFound item = new LostFound();
        item.setTitle(title);
        item.setType(type);
        item.setStatus(status);
        item.setLocation(location);
        item.setCoverImg(asString(body.get("cover_img")));
        item.setDescription(description);
        item.setUserId(userId);
        item.setModerationStatus("pending");
        item.setCreatedAt(LocalDateTime.now());
        item.setUpdatedAt(LocalDateTime.now());
        lostFoundMapper.insert(item);
        return Result.ok("发布成功，等待管理员审核", item);
    }

    private String asString(Object value) {
        return value == null ? null : String.valueOf(value);
    }

    private Map<Long, User> loadUsers(List<LostFound> records) {
        Set<Long> userIds = records.stream()
                .map(LostFound::getUserId)
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());
        if (userIds.isEmpty()) {
            return Map.of();
        }
        return userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity()));
    }

    private Map<String, Object> toListItem(LostFound item, User user) {
        Map<String, Object> row = new LinkedHashMap<>();
        row.put("id", item.getId());
        row.put("title", item.getTitle());
        row.put("type", item.getType());
        row.put("status", item.getStatus());
        row.put("location", item.getLocation());
        row.put("coverImg", item.getCoverImg());
        row.put("description", item.getDescription());
        row.put("created_at", item.getCreatedAt() == null ? "" : item.getCreatedAt().format(DATE_TIME));
        if (user != null) {
            Map<String, Object> author = new LinkedHashMap<>();
            author.put("id", user.getId());
            author.put("username", user.getUsername());
            author.put("name", user.getName());
            row.put("user", author);
        } else {
            row.put("user", null);
        }
        return row;
    }
}
