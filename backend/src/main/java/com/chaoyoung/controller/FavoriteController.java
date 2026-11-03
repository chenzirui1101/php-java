package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Favorite;
import com.chaoyoung.entity.Product;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.FavoriteMapper;
import com.chaoyoung.mapper.ProductMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 商品收藏接口，路由与 Laravel FavoriteController 对齐：/api/favorites/*
 * - GET    /api/favorites                  收藏列表
 * - POST   /api/favorites                  添加收藏
 * - DELETE /api/favorites/{productId}      取消收藏
 * - GET    /api/favorites/check/{productId}  检查单个商品
 * - POST   /api/favorites/check/batch        批量检查
 */
@RestController
@RequestMapping("/api/favorites")
@RequiredArgsConstructor
public class FavoriteController {

    private static final DateTimeFormatter DATE_FORMATTER = DateTimeFormatter.ofPattern("yyyy-MM-dd");
    private static final Map<Integer, String> CATEGORIES = Map.of(
            1, "数码产品",
            2, "生活用品",
            3, "学习资料",
            4, "服饰鞋包",
            5, "其他");

    private final FavoriteMapper favoriteMapper;
    private final ProductMapper productMapper;
    private final UserMapper userMapper;

    @GetMapping
    public Result<Map<String, Object>> index(@RequestParam(defaultValue = "1") int pageNum,
                                             @RequestParam(defaultValue = "12") int pageSize) {
        Long userId = requireUserId();

        Page<Favorite> page = favoriteMapper.selectPage(
                new Page<>(Math.max(pageNum, 1), Math.max(1, Math.min(pageSize, 100))),
                new LambdaQueryWrapper<Favorite>()
                        .eq(Favorite::getUserId, userId)
                        .orderByDesc(Favorite::getCreatedAt));

        List<Map<String, Object>> list = new ArrayList<>();
        List<Favorite> records = page.getRecords();
        if (!records.isEmpty()) {
            Set<Long> productIds = records.stream().map(Favorite::getProductId).collect(Collectors.toSet());
            Map<Long, Product> products = productMapper.selectBatchIds(productIds).stream()
                    .collect(Collectors.toMap(Product::getId, Function.identity()));
            Map<Long, User> sellers = loadSellers(new ArrayList<>(products.values()));

            for (Favorite favorite : records) {
                Product product = products.get(favorite.getProductId());
                if (product != null) {
                    list.add(toListItem(product, sellers.get(product.getUserId())));
                }
            }
        }

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("list", list);
        data.put("total", page.getTotal());
        return Result.ok("success", data);
    }

    @PostMapping
    public Result<Favorite> store(@RequestBody(required = false) Map<String, Object> body) {
        Long userId = requireUserId();
        Long productId = toLong(body == null ? null : body.get("product_id"));
        if (productId == null) {
            throw new BusinessException(422, "商品ID不能为空");
        }
        if (productMapper.selectById(productId) == null) {
            throw new BusinessException(404, "商品不存在");
        }
        if (favoriteMapper.selectCount(favoriteQuery(userId, productId)) > 0) {
            throw new BusinessException(400, "商品已收藏");
        }

        Favorite favorite = new Favorite();
        favorite.setUserId(userId);
        favorite.setProductId(productId);
        favorite.setCreatedAt(LocalDateTime.now());
        favorite.setUpdatedAt(LocalDateTime.now());
        favoriteMapper.insert(favorite);
        return Result.ok("商品收藏成功", favorite);
    }

    @DeleteMapping("/{productId}")
    public Result<Void> destroy(@PathVariable Long productId) {
        Long userId = requireUserId();
        if (favoriteMapper.selectCount(favoriteQuery(userId, productId)) == 0) {
            throw new BusinessException(404, "未找到收藏记录");
        }
        favoriteMapper.delete(favoriteQuery(userId, productId));
        return Result.ok("取消收藏成功", null);
    }

    @GetMapping("/check/{productId}")
    public Result<Map<String, Object>> check(@PathVariable Long productId) {
        Long userId = requireUserId();
        Map<String, Object> data = new LinkedHashMap<>();
        data.put("is_favorite", favoriteMapper.selectCount(favoriteQuery(userId, productId)) > 0);
        return Result.ok("检查收藏状态成功", data);
    }

    @PostMapping("/check/batch")
    public Result<Map<String, Boolean>> batchCheck(@RequestBody(required = false) Map<String, Object> body) {
        Long userId = requireUserId();
        Map<String, Boolean> result = new LinkedHashMap<>();

        Object raw = body == null ? null : body.get("product_ids");
        if (!(raw instanceof List<?> productIds) || productIds.isEmpty()) {
            return Result.ok("批量检查收藏状态成功", result);
        }

        List<Long> ids = productIds.stream().map(this::toLong).filter(Objects::nonNull).toList();
        if (ids.isEmpty()) {
            return Result.ok("批量检查收藏状态成功", result);
        }

        Set<Long> favorited = favoriteMapper.selectList(new LambdaQueryWrapper<Favorite>()
                        .eq(Favorite::getUserId, userId)
                        .in(Favorite::getProductId, ids))
                .stream()
                .map(Favorite::getProductId)
                .collect(Collectors.toSet());

        for (Long id : ids) {
            result.put(String.valueOf(id), favorited.contains(id));
        }
        return Result.ok("批量检查收藏状态成功", result);
    }

    private Long requireUserId() {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        return userId;
    }

    private LambdaQueryWrapper<Favorite> favoriteQuery(Long userId, Long productId) {
        return new LambdaQueryWrapper<Favorite>()
                .eq(Favorite::getUserId, userId)
                .eq(Favorite::getProductId, productId);
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

    private Map<Long, User> loadSellers(List<Product> products) {
        Set<Long> userIds = products.stream()
                .map(Product::getUserId)
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());
        if (userIds.isEmpty()) {
            return Map.of();
        }
        return userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity(), (a, b) -> a));
    }

    private Map<String, Object> toListItem(Product product, User seller) {
        Map<String, Object> item = new LinkedHashMap<>();
        item.put("id", product.getId());
        item.put("title", product.getTitle());
        item.put("price", formatPrice(product.getPrice()));
        item.put("category", categoryName(product.getCategoryId()));
        item.put("categoryId", product.getCategoryId());
        item.put("coverImg", imageUrl(product.getCoverImg()));
        item.put("createTime", product.getCreatedAt() == null ? ""
                : product.getCreatedAt().format(DATE_FORMATTER));
        item.put("sellerName", sellerName(seller));
        item.put("sellerUsername", sellerUsername(seller));
        return item;
    }

    private String sellerName(User seller) {
        if (seller == null) {
            return "未知用户";
        }
        return StringUtils.hasText(seller.getName()) ? seller.getName() : seller.getUsername();
    }

    private String sellerUsername(User seller) {
        return seller == null ? "未知用户" : seller.getUsername();
    }

    private String categoryName(Integer categoryId) {
        return categoryId == null ? "其他" : CATEGORIES.getOrDefault(categoryId, "其他");
    }

    private String formatPrice(BigDecimal price) {
        return price == null ? "0.00" : price.setScale(2, RoundingMode.HALF_UP).toPlainString();
    }

    private String imageUrl(String imagePath) {
        if (!StringUtils.hasText(imagePath)) {
            return "";
        }
        if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
            return imagePath;
        }
        String path = imagePath.startsWith("/") ? imagePath.substring(1) : imagePath;
        return "/storage/" + path;
    }
}
