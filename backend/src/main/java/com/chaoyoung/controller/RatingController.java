package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.core.conditions.update.LambdaUpdateWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Product;
import com.chaoyoung.entity.Rating;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.ProductMapper;
import com.chaoyoung.mapper.RatingMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 商品评价接口，对齐 Laravel RatingController。
 */
@RestController
@RequestMapping("/api")
@RequiredArgsConstructor
public class RatingController {

    private static final DateTimeFormatter DATE_TIME = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");

    private final RatingMapper ratingMapper;
    private final ProductMapper productMapper;
    private final UserMapper userMapper;

    @GetMapping("/products/{productId}/ratings")
    public Result<Map<String, Object>> productRatings(@PathVariable Long productId,
                                                      @RequestParam(defaultValue = "1") int page,
                                                      @RequestParam(name = "per_page", defaultValue = "10") int perPage) {
        Product product = productMapper.selectById(productId);
        if (product == null) {
            throw new BusinessException(404, "商品不存在");
        }

        Page<Rating> result = ratingMapper.selectPage(new Page<>(page, perPage),
                new LambdaQueryWrapper<Rating>()
                        .eq(Rating::getProductId, productId)
                        .eq(Rating::getStatus, "approved")
                        .orderByDesc(Rating::getCreatedAt));

        Map<Long, User> users = loadUsers(result.getRecords().stream()
                .map(Rating::getUserId).collect(Collectors.toSet()));

        List<Map<String, Object>> list = result.getRecords().stream()
                .map(rating -> toRow(rating, users.get(rating.getUserId())))
                .collect(Collectors.toList());

        return Result.ok("获取商品评价列表成功",
                pageData(list, page, perPage, result.getTotal()));
    }

    @PostMapping("/ratings")
    public Result<Rating> create(@RequestBody(required = false) Map<String, Object> body) {
        Long userId = requireLogin();
        if (body == null) {
            throw new BusinessException(422, "请求参数不能为空");
        }

        Long productId = longValue(body.get("product_id"));
        if (productId == null) {
            throw new BusinessException(422, "商品 ID 不能为空");
        }
        if (productMapper.selectById(productId) == null) {
            throw new BusinessException(404, "商品不存在");
        }

        int score = intValue(body.get("rating"), -1);
        if (score < 1 || score > 5) {
            throw new BusinessException(422, "评分必须在 1-5 之间");
        }

        Long exists = ratingMapper.selectCount(new LambdaQueryWrapper<Rating>()
                .eq(Rating::getUserId, userId)
                .eq(Rating::getProductId, productId));
        if (exists != null && exists > 0) {
            throw new BusinessException(400, "您已评价过该商品");
        }

        Rating rating = new Rating();
        rating.setUserId(userId);
        rating.setProductId(productId);
        rating.setRating(score);
        rating.setComment(stringValue(body.get("comment")));
        rating.setStatus("pending");
        rating.setCreatedAt(LocalDateTime.now());
        rating.setUpdatedAt(LocalDateTime.now());
        ratingMapper.insert(rating);

        return Result.ok("评价已提交，等待管理员审核", rating);
    }

    @GetMapping("/ratings/user")
    public Result<Map<String, Object>> userRatings(@RequestParam(defaultValue = "1") int page,
                                                   @RequestParam(name = "per_page", defaultValue = "10") int perPage) {
        Long userId = requireLogin();

        Page<Rating> result = ratingMapper.selectPage(new Page<>(page, perPage),
                new LambdaQueryWrapper<Rating>()
                        .eq(Rating::getUserId, userId)
                        .orderByDesc(Rating::getCreatedAt));

        List<Map<String, Object>> list = result.getRecords().stream()
                .map(rating -> {
                    Map<String, Object> row = toRow(rating, null);
                    Product product = productMapper.selectById(rating.getProductId());
                    if (product != null) {
                        Map<String, Object> productRow = new LinkedHashMap<>();
                        productRow.put("id", product.getId());
                        productRow.put("title", product.getTitle());
                        productRow.put("coverImg", product.getCoverImg());
                        productRow.put("price", product.getPrice());
                        row.put("product", productRow);
                    }
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("获取用户评价列表成功",
                pageData(list, page, perPage, result.getTotal()));
    }

    @PutMapping("/ratings/{id}")
    public Result<Rating> update(@PathVariable Long id,
                                 @RequestBody(required = false) Map<String, Object> body) {
        Long userId = requireLogin();
        Rating rating = ratingMapper.selectOne(new LambdaQueryWrapper<Rating>()
                .eq(Rating::getId, id)
                .eq(Rating::getUserId, userId));
        if (rating == null) {
            throw new BusinessException(404, "评价不存在或无权限修改");
        }
        if (body == null) {
            throw new BusinessException(422, "请求参数不能为空");
        }

        int score = intValue(body.get("rating"), -1);
        if (score < 1 || score > 5) {
            throw new BusinessException(422, "评分必须在 1-5 之间");
        }

        ratingMapper.update(null, new LambdaUpdateWrapper<Rating>()
                .eq(Rating::getId, id)
                .set(Rating::getRating, score)
                .set(Rating::getComment, stringValue(body.get("comment")))
                .set(Rating::getStatus, "pending")
                .set(Rating::getUpdatedAt, LocalDateTime.now()));

        rating.setRating(score);
        rating.setComment(stringValue(body.get("comment")));
        rating.setStatus("pending");
        return Result.ok("评价已更新，等待管理员审核", rating);
    }

    @DeleteMapping("/ratings/{id}")
    public Result<Void> delete(@PathVariable Long id) {
        Long userId = requireLogin();
        Rating rating = ratingMapper.selectOne(new LambdaQueryWrapper<Rating>()
                .eq(Rating::getId, id)
                .eq(Rating::getUserId, userId));
        if (rating == null) {
            throw new BusinessException(404, "评价不存在或无权限删除");
        }
        ratingMapper.deleteById(id);
        return Result.ok("评价删除成功", null);
    }

    private Long requireLogin() {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        return userId;
    }

    private Map<String, Object> toRow(Rating rating, User user) {
        Map<String, Object> row = new LinkedHashMap<>();
        row.put("id", rating.getId());
        row.put("user_id", rating.getUserId());
        row.put("product_id", rating.getProductId());
        row.put("rating", rating.getRating());
        row.put("comment", rating.getComment());
        row.put("status", rating.getStatus());
        row.put("created_at", rating.getCreatedAt() == null ? "" : rating.getCreatedAt().format(DATE_TIME));
        row.put("updated_at", rating.getUpdatedAt() == null ? "" : rating.getUpdatedAt().format(DATE_TIME));

        Map<String, Object> userRow = new LinkedHashMap<>();
        userRow.put("id", rating.getUserId());
        userRow.put("name", user == null ? "匿名用户" : displayName(user));
        userRow.put("username", user == null ? "" : user.getUsername());
        userRow.put("avatar", user == null ? "" : (user.getAvatar() == null ? "" : user.getAvatar()));
        row.put("user", userRow);
        return row;
    }

    private String displayName(User user) {
        return user.getName() == null || user.getName().isBlank() ? user.getUsername() : user.getName();
    }

    private Map<String, Object> pageData(List<Map<String, Object>> list, int page, int perPage, long total) {
        Map<String, Object> data = new LinkedHashMap<>();
        data.put("current_page", page);
        data.put("data", list);
        data.put("total", total);
        data.put("per_page", perPage);
        data.put("last_page", perPage <= 0 ? 0 : (total + perPage - 1) / perPage);
        return data;
    }

    private Map<Long, User> loadUsers(Set<Long> userIds) {
        if (userIds.isEmpty()) {
            return Map.of();
        }
        return userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity(), (a, b) -> a));
    }

    private Long longValue(Object value) {
        if (value instanceof Number number) {
            return number.longValue();
        }
        if (value instanceof String text && !text.isBlank()) {
            try {
                return Long.parseLong(text.trim());
            } catch (NumberFormatException ignored) {
                return null;
            }
        }
        return null;
    }

    private int intValue(Object value, int defaultValue) {
        if (value instanceof Number number) {
            return number.intValue();
        }
        if (value instanceof String text && !text.isBlank()) {
            try {
                return Integer.parseInt(text.trim());
            } catch (NumberFormatException ignored) {
                return defaultValue;
            }
        }
        return defaultValue;
    }

    private String stringValue(Object value) {
        return value == null ? null : String.valueOf(value);
    }
}
