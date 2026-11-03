package com.chaoyoung.service;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.dto.ProductRequest;
import com.chaoyoung.entity.Product;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.ProductMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.StringUtils;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 商品业务，接口字段对齐 Laravel ProductController。
 */
@Service
@RequiredArgsConstructor
public class ProductService {

    private static final DateTimeFormatter DATE_FORMATTER = DateTimeFormatter.ofPattern("yyyy-MM-dd");
    private static final Map<Integer, String> CATEGORIES = Map.of(
            1, "数码产品",
            2, "生活用品",
            3, "学习资料",
            4, "服饰鞋包",
            5, "其他");

    private final ProductMapper productMapper;
    private final UserMapper userMapper;

    public Map<String, Object> list(int pageNum, int pageSize, int categoryId, String keyword) {
        Page<Product> page = new Page<>(Math.max(pageNum, 1), Math.max(1, Math.min(pageSize, 100)));
        LambdaQueryWrapper<Product> wrapper = new LambdaQueryWrapper<Product>()
                .eq(Product::getStatus, "approved")
                .eq(categoryId > 0, Product::getCategoryId, categoryId)
                .and(StringUtils.hasText(keyword), query -> query
                        .like(Product::getTitle, keyword)
                        .or()
                        .like(Product::getDescription, keyword))
                .orderByDesc(Product::getCreatedAt);

        Page<Product> result = productMapper.selectPage(page, wrapper);
        Map<Long, User> users = loadUsers(result.getRecords());
        List<Map<String, Object>> list = result.getRecords().stream()
                .map(product -> toListItem(product, users.get(product.getUserId())))
                .toList();

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("list", list);
        data.put("total", result.getTotal());
        return data;
    }

    public Map<String, Object> detail(Long id) {
        Product product = productMapper.selectById(id);
        if (product == null) {
            throw new BusinessException(404, "商品不存在");
        }
        Long currentUserId = SecurityUtils.currentUserId();
        if (!"approved".equals(product.getStatus()) && !Objects.equals(product.getUserId(), currentUserId)) {
            throw new BusinessException(403, "无权访问该商品");
        }

        User seller = userMapper.selectById(product.getUserId());
        Map<String, Object> data = new LinkedHashMap<>();
        data.put("id", product.getId());
        data.put("title", product.getTitle());
        data.put("price", formatPrice(product.getPrice()));
        data.put("stock", product.getStock());
        data.put("category", categoryName(product.getCategoryId()));
        data.put("categoryId", product.getCategoryId());
        data.put("coverImg", imageUrl(product.getCoverImg()));
        data.put("description", product.getDescription() == null ? "" : product.getDescription());
        data.put("createTime", product.getCreatedAt() == null ? ""
                : product.getCreatedAt().format(DATE_FORMATTER));
        data.put("seller_id", product.getUserId());
        data.put("sellerName", sellerName(seller));
        data.put("sellerUsername", sellerUsername(seller));
        return data;
    }

    @Transactional(rollbackFor = Exception.class)
    public Product create(ProductRequest request) {
        Long userId = requireCurrentUserId();
        Product product = new Product();
        applyRequest(product, request);
        product.setUserId(userId);
        product.setStatus("pending");
        product.setStock(request.getStock() == null ? 0 : request.getStock());
        productMapper.insert(product);
        return product;
    }

    @Transactional(rollbackFor = Exception.class)
    public Product update(Long id, ProductRequest request) {
        Product product = requireOwnedProduct(id, "无权修改该商品");
        applyRequest(product, request);
        if (request.getStock() != null) {
            product.setStock(request.getStock());
        }
        // 修改后重新审核，与 Laravel 一致
        product.setStatus("pending");
        productMapper.updateById(product);
        return product;
    }

    @Transactional(rollbackFor = Exception.class)
    public void delete(Long id) {
        requireOwnedProduct(id, "无权删除该商品");
        productMapper.deleteById(id);
    }

    public Map<String, Object> myProducts() {
        Long userId = requireCurrentUserId();
        List<Product> products = productMapper.selectList(new LambdaQueryWrapper<Product>()
                .eq(Product::getUserId, userId)
                .orderByDesc(Product::getCreatedAt));

        List<Map<String, Object>> list = products.stream().map(product -> {
            Map<String, Object> item = new LinkedHashMap<>();
            item.put("id", product.getId());
            item.put("title", product.getTitle());
            item.put("price", formatPrice(product.getPrice()));
            item.put("stock", product.getStock());
            item.put("category", categoryName(product.getCategoryId()));
            item.put("categoryId", product.getCategoryId());
            item.put("coverImg", imageUrl(product.getCoverImg()));
            item.put("createTime", product.getCreatedAt() == null ? ""
                    : product.getCreatedAt().format(DATE_FORMATTER));
            item.put("status", product.getStatus());
            item.put("statusText", statusText(product.getStatus()));
            return item;
        }).toList();

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("list", list);
        data.put("total", list.size());
        return data;
    }

    private void applyRequest(Product product, ProductRequest request) {
        product.setTitle(request.getTitle());
        product.setCategoryId(request.getCategoryId());
        product.setPrice(request.getPrice());
        product.setCoverImg(request.getCoverImg());
        product.setDescription(request.getDescription());
    }

    private Product requireOwnedProduct(Long id, String message) {
        Long userId = requireCurrentUserId();
        Product product = productMapper.selectById(id);
        if (product == null || !Objects.equals(product.getUserId(), userId)) {
            throw new BusinessException(403, message);
        }
        return product;
    }

    private Long requireCurrentUserId() {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "未登录或登录已过期");
        }
        return userId;
    }

    private Map<Long, User> loadUsers(List<Product> products) {
        List<Long> userIds = products.stream()
                .map(Product::getUserId)
                .filter(Objects::nonNull)
                .distinct()
                .collect(Collectors.toList());
        if (userIds.isEmpty()) {
            return Map.of();
        }
        List<User> users = userMapper.selectBatchIds(userIds);
        return users.stream().collect(Collectors.toMap(User::getId, Function.identity(), (a, b) -> a));
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

    private String statusText(String status) {
        return switch (status == null ? "" : status) {
            case "pending" -> "待审核";
            case "approved" -> "已通过";
            case "rejected" -> "已拒绝";
            default -> "未知";
        };
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
