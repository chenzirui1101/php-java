package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.core.conditions.update.LambdaUpdateWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Activity;
import com.chaoyoung.entity.LostFound;
import com.chaoyoung.entity.Product;
import com.chaoyoung.entity.Rating;
import com.chaoyoung.entity.Reply;
import com.chaoyoung.entity.Topic;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.ActivityMapper;
import com.chaoyoung.mapper.LostFoundMapper;
import com.chaoyoung.mapper.ProductMapper;
import com.chaoyoung.mapper.RatingMapper;
import com.chaoyoung.mapper.ReplyMapper;
import com.chaoyoung.mapper.TopicMapper;
import com.chaoyoung.mapper.UserMapper;
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
import java.util.Comparator;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 管理员后台接口，对齐 Laravel AdminController / ContentModerationController 的响应字段。
 */
@RestController
@RequestMapping("/api/admin")
@RequiredArgsConstructor
public class AdminController {

    private static final DateTimeFormatter DATE = DateTimeFormatter.ofPattern("yyyy-MM-dd");
    private static final DateTimeFormatter MINUTE = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm");
    private static final DateTimeFormatter SECOND = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");
    private static final Map<Integer, String> PRODUCT_CATEGORIES = Map.of(
            1, "数码产品",
            2, "生活用品",
            3, "学习资料",
            4, "服饰鞋包",
            5, "其他");

    private final ProductMapper productMapper;
    private final UserMapper userMapper;
    private final LostFoundMapper lostFoundMapper;
    private final TopicMapper topicMapper;
    private final ReplyMapper replyMapper;
    private final ActivityMapper activityMapper;
    private final RatingMapper ratingMapper;

    @GetMapping("/products")
    public Result<Map<String, Object>> products(@RequestParam(defaultValue = "1") int pageNum,
                                                @RequestParam(defaultValue = "12") int pageSize,
                                                @RequestParam(defaultValue = "") String keyword) {
        LambdaQueryWrapper<Product> query = new LambdaQueryWrapper<>();
        if (StringUtils.hasText(keyword)) {
            query.and(q -> q.like(Product::getTitle, keyword).or().like(Product::getDescription, keyword));
        }
        query.orderByDesc(Product::getCreatedAt);

        Page<Product> page = productMapper.selectPage(new Page<>(pageNum, pageSize), query);
        Map<Long, User> users = loadUsers(page.getRecords().stream().map(Product::getUserId).collect(Collectors.toSet()));

        List<Map<String, Object>> list = page.getRecords().stream()
                .map(product -> {
                    User seller = users.get(product.getUserId());
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", product.getId());
                    row.put("title", product.getTitle());
                    row.put("price", formatPrice(product.getPrice()));
                    row.put("category", categoryName(product.getCategoryId()));
                    row.put("categoryId", product.getCategoryId());
                    row.put("coverImg", imageUrl(product.getCoverImg()));
                    row.put("createTime", product.getCreatedAt() == null ? "" : product.getCreatedAt().format(DATE));
                    row.put("sellerName", displayName(seller));
                    row.put("sellerUsername", seller == null ? "未知用户" : seller.getUsername());
                    row.put("sellerId", product.getUserId());
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("success", pageData(list, page.getTotal()));
    }

    @DeleteMapping("/products/{id}")
    public Result<Void> deleteProduct(@PathVariable Long id) {
        if (productMapper.selectById(id) == null) {
            throw new BusinessException(404, "商品不存在");
        }
        productMapper.deleteById(id);
        return Result.ok("商品删除成功", null);
    }

    @GetMapping("/users")
    public Result<Map<String, Object>> users(@RequestParam(defaultValue = "1") int pageNum,
                                             @RequestParam(defaultValue = "12") int pageSize,
                                             @RequestParam(defaultValue = "") String keyword) {
        LambdaQueryWrapper<User> query = new LambdaQueryWrapper<>();
        if (StringUtils.hasText(keyword)) {
            query.and(q -> q.like(User::getName, keyword)
                    .or().like(User::getUsername, keyword)
                    .or().like(User::getEmail, keyword));
        }
        query.orderByDesc(User::getCreatedAt);

        Page<User> page = userMapper.selectPage(new Page<>(pageNum, pageSize), query);
        List<Map<String, Object>> list = page.getRecords().stream()
                .map(user -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", user.getId());
                    row.put("name", user.getName());
                    row.put("username", user.getUsername());
                    row.put("email", user.getEmail());
                    row.put("createTime", user.getCreatedAt() == null ? "" : user.getCreatedAt().format(DATE));
                    row.put("productCount", productMapper.selectCount(
                            new LambdaQueryWrapper<Product>().eq(Product::getUserId, user.getId())));
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("success", pageData(list, page.getTotal()));
    }

    @GetMapping("/lostfound")
    public Result<Map<String, Object>> lostFound(@RequestParam(defaultValue = "1") int pageNum,
                                                 @RequestParam(defaultValue = "10") int pageSize,
                                                 @RequestParam(defaultValue = "") String keyword) {
        LambdaQueryWrapper<LostFound> query = new LambdaQueryWrapper<>();
        if (StringUtils.hasText(keyword)) {
            query.and(q -> q.like(LostFound::getTitle, keyword).or().like(LostFound::getDescription, keyword));
        }
        query.orderByDesc(LostFound::getCreatedAt);

        Page<LostFound> page = lostFoundMapper.selectPage(new Page<>(pageNum, pageSize), query);
        Map<Long, User> users = loadUsers(page.getRecords().stream().map(LostFound::getUserId).collect(Collectors.toSet()));

        List<Map<String, Object>> list = page.getRecords().stream()
                .map(item -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", item.getId());
                    row.put("title", item.getTitle());
                    row.put("type", item.getType());
                    row.put("coverImg", imageUrl(item.getCoverImg()));
                    row.put("publisherName", displayName(users.get(item.getUserId())));
                    row.put("createTime", item.getCreatedAt() == null ? "" : item.getCreatedAt().format(MINUTE));
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("success", pageData(list, page.getTotal()));
    }

    @DeleteMapping("/lostfound/{id}")
    public Result<Void> deleteLostFound(@PathVariable Long id) {
        if (lostFoundMapper.selectById(id) == null) {
            throw new BusinessException(404, "失物不存在");
        }
        lostFoundMapper.deleteById(id);
        return Result.ok("删除成功", null);
    }

    @GetMapping("/forum")
    public Result<Map<String, Object>> forum(@RequestParam(defaultValue = "1") int pageNum,
                                             @RequestParam(defaultValue = "10") int pageSize,
                                             @RequestParam(defaultValue = "") String keyword) {
        LambdaQueryWrapper<Topic> query = new LambdaQueryWrapper<>();
        if (StringUtils.hasText(keyword)) {
            query.and(q -> q.like(Topic::getTitle, keyword).or().like(Topic::getContent, keyword));
        }
        query.orderByDesc(Topic::getCreatedAt);

        Page<Topic> page = topicMapper.selectPage(new Page<>(pageNum, pageSize), query);
        Map<Long, User> users = loadUsers(page.getRecords().stream().map(Topic::getUserId).collect(Collectors.toSet()));

        List<Map<String, Object>> list = page.getRecords().stream()
                .map(topic -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", topic.getId());
                    row.put("title", topic.getTitle());
                    row.put("category", "论坛");
                    row.put("publisherName", displayName(users.get(topic.getUserId())));
                    row.put("replyCount", topic.getRepliesCount() == null ? 0 : topic.getRepliesCount());
                    row.put("createTime", topic.getCreatedAt() == null ? "" : topic.getCreatedAt().format(MINUTE));
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("success", pageData(list, page.getTotal()));
    }

    @DeleteMapping("/forum/{id}")
    public Result<Void> deleteForum(@PathVariable Long id) {
        if (topicMapper.selectById(id) == null) {
            throw new BusinessException(404, "话题不存在");
        }
        topicMapper.deleteById(id);
        return Result.ok("删除成功", null);
    }

    @GetMapping("/activities")
    public Result<Map<String, Object>> activities(@RequestParam(defaultValue = "1") int pageNum,
                                                  @RequestParam(defaultValue = "10") int pageSize,
                                                  @RequestParam(defaultValue = "") String keyword) {
        LambdaQueryWrapper<Activity> query = new LambdaQueryWrapper<>();
        if (StringUtils.hasText(keyword)) {
            query.and(q -> q.like(Activity::getTitle, keyword).or().like(Activity::getDescription, keyword));
        }
        query.orderByDesc(Activity::getCreatedAt);

        Page<Activity> page = activityMapper.selectPage(new Page<>(pageNum, pageSize), query);
        Map<Long, User> users = loadUsers(page.getRecords().stream().map(Activity::getUserId).collect(Collectors.toSet()));

        List<Map<String, Object>> list = page.getRecords().stream()
                .map(activity -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", activity.getId());
                    row.put("title", activity.getTitle());
                    row.put("coverImg", imageUrl(activity.getPoster()));
                    row.put("publisherName", displayName(users.get(activity.getUserId())));
                    row.put("activityTime", activity.getStartTime() == null ? "" : activity.getStartTime().format(MINUTE));
                    row.put("createTime", activity.getCreatedAt() == null ? "" : activity.getCreatedAt().format(MINUTE));
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("success", pageData(list, page.getTotal()));
    }

    @DeleteMapping("/activities/{id}")
    public Result<Void> deleteActivity(@PathVariable Long id) {
        if (activityMapper.selectById(id) == null) {
            throw new BusinessException(404, "活动不存在");
        }
        activityMapper.deleteById(id);
        return Result.ok("删除成功", null);
    }

    @GetMapping("/moderation/pending")
    public Result<Map<String, Object>> pending(@RequestParam(defaultValue = "all") String type,
                                               @RequestParam(defaultValue = "1") int page,
                                               @RequestParam(defaultValue = "10") int pageSize) {
        boolean all = "all".equals(type);

        List<Product> products = all || "product".equals(type)
                ? productMapper.selectList(new LambdaQueryWrapper<Product>()
                        .eq(Product::getStatus, "pending").orderByDesc(Product::getCreatedAt))
                : List.of();
        List<Topic> topics = all || "topic".equals(type)
                ? topicMapper.selectList(new LambdaQueryWrapper<Topic>()
                        .eq(Topic::getStatus, "pending").orderByDesc(Topic::getCreatedAt))
                : List.of();
        List<Reply> replies = all || "comment".equals(type)
                ? replyMapper.selectList(new LambdaQueryWrapper<Reply>()
                        .eq(Reply::getStatus, "pending").orderByDesc(Reply::getCreatedAt))
                : List.of();
        List<LostFound> lostFounds = all || "lostfound".equals(type)
                ? lostFoundMapper.selectList(new LambdaQueryWrapper<LostFound>()
                        .eq(LostFound::getModerationStatus, "pending").orderByDesc(LostFound::getCreatedAt))
                : List.of();
        List<Activity> activities = all || "activity".equals(type)
                ? activityMapper.selectList(new LambdaQueryWrapper<Activity>()
                        .eq(Activity::getModerationStatus, "pending").orderByDesc(Activity::getCreatedAt))
                : List.of();
        List<Rating> ratings = all || "rating".equals(type)
                ? ratingMapper.selectList(new LambdaQueryWrapper<Rating>()
                        .eq(Rating::getStatus, "pending").orderByDesc(Rating::getCreatedAt))
                : List.of();

        Set<Long> userIds = new java.util.HashSet<>();
        products.forEach(item -> addIfNotNull(userIds, item.getUserId()));
        topics.forEach(item -> addIfNotNull(userIds, item.getUserId()));
        replies.forEach(item -> addIfNotNull(userIds, item.getUserId()));
        lostFounds.forEach(item -> addIfNotNull(userIds, item.getUserId()));
        activities.forEach(item -> addIfNotNull(userIds, item.getUserId()));
        ratings.forEach(item -> addIfNotNull(userIds, item.getUserId()));
        Map<Long, User> users = loadUsers(userIds);
        Map<Long, Product> ratingProducts = loadProducts(ratings.stream()
                .map(Rating::getProductId).collect(Collectors.toSet()));

        List<PendingItem> items = new ArrayList<>();
        products.forEach(item -> items.add(new PendingItem(item.getCreatedAt(), pendingRow("product",
                item.getId(), item.getTitle(), item.getDescription(),
                imageUrl(item.getCoverImg()), users.get(item.getUserId())))));
        topics.forEach(item -> items.add(new PendingItem(item.getCreatedAt(), pendingRow("topic",
                item.getId(), item.getTitle(), item.getContent(),
                null, users.get(item.getUserId())))));
        replies.forEach(item -> items.add(new PendingItem(item.getCreatedAt(), pendingRow("comment",
                item.getId(), "评论 #" + item.getId(), item.getContent(),
                null, users.get(item.getUserId())))));
        lostFounds.forEach(item -> items.add(new PendingItem(item.getCreatedAt(), pendingRow("lostfound",
                item.getId(), item.getTitle(), item.getDescription(),
                imageUrl(item.getCoverImg()), users.get(item.getUserId())))));
        activities.forEach(item -> items.add(new PendingItem(item.getCreatedAt(), pendingRow("activity",
                item.getId(), item.getTitle(), item.getDescription(),
                imageUrl(item.getPoster()), users.get(item.getUserId())))));
        ratings.forEach(item -> {
            Product product = ratingProducts.get(item.getProductId());
            String title = product == null ? "商品评价 #" + item.getId() : product.getTitle();
            if (item.getRating() != null) {
                title = "★" + item.getRating() + " · " + title;
            }
            items.add(new PendingItem(item.getCreatedAt(), pendingRow("rating", item.getId(), title,
                    item.getComment(), imageUrl(product == null ? null : product.getCoverImg()),
                    users.get(item.getUserId()))));
        });

        items.sort(Comparator.comparing(PendingItem::createdAt,
                Comparator.nullsLast(Comparator.reverseOrder())));

        int from = Math.min(Math.max(page - 1, 0) * pageSize, items.size());
        int to = Math.min(from + pageSize, items.size());
        List<Map<String, Object>> list = items.subList(from, to).stream()
                .map(item -> {
                    Map<String, Object> row = item.row();
                    row.put("created_at", item.createdAt() == null ? "" : item.createdAt().format(SECOND));
                    return row;
                })
                .collect(Collectors.toList());

        return Result.ok("success", pageData(list, items.size()));
    }

    @PostMapping("/moderation/approve/{type}/{id}")
    public Result<Void> approve(@PathVariable String type, @PathVariable Long id) {
        updateModerationStatus(type, id, "approved");
        return Result.ok("审核通过", null);
    }

    @PostMapping("/moderation/reject/{type}/{id}")
    public Result<Void> reject(@PathVariable String type, @PathVariable Long id,
                               @RequestBody(required = false) Map<String, Object> body) {
        updateModerationStatus(type, id, "rejected");
        return Result.ok("已拒绝", null);
    }

    private void updateModerationStatus(String type, Long id, String status) {
        LocalDateTime now = LocalDateTime.now();
        int affected;
        switch (type) {
            case "product" -> affected = productMapper.update(null, new LambdaUpdateWrapper<Product>()
                    .eq(Product::getId, id)
                    .set(Product::getStatus, status)
                    .set(Product::getUpdatedAt, now));
            case "topic" -> affected = topicMapper.update(null, new LambdaUpdateWrapper<Topic>()
                    .eq(Topic::getId, id)
                    .set(Topic::getStatus, status)
                    .set(Topic::getUpdatedAt, now));
            case "comment" -> affected = replyMapper.update(null, new LambdaUpdateWrapper<Reply>()
                    .eq(Reply::getId, id)
                    .set(Reply::getStatus, status)
                    .set(Reply::getUpdatedAt, now));
            case "lostfound" -> affected = lostFoundMapper.update(null, new LambdaUpdateWrapper<LostFound>()
                    .eq(LostFound::getId, id)
                    .set(LostFound::getModerationStatus, status)
                    .set(LostFound::getUpdatedAt, now));
            case "activity" -> affected = activityMapper.update(null, new LambdaUpdateWrapper<Activity>()
                    .eq(Activity::getId, id)
                    .set(Activity::getModerationStatus, status)
                    .set(Activity::getUpdatedAt, now));
            case "rating" -> affected = ratingMapper.update(null, new LambdaUpdateWrapper<Rating>()
                    .eq(Rating::getId, id)
                    .set(Rating::getStatus, status)
                    .set(Rating::getUpdatedAt, now));
            default -> throw new BusinessException(422, "不支持的内容类型");
        }
        if (affected == 0) {
            throw new BusinessException(404, "内容不存在");
        }
    }

    private Map<String, Object> pendingRow(String type, Long id, String title, String content,
                                           String image, User publisher) {
        Map<String, Object> row = new LinkedHashMap<>();
        row.put("id", id);
        row.put("type", type);
        row.put("title", title);
        row.put("content", content == null ? "" : content);
        row.put("image", image == null ? "" : image);
        row.put("author", displayName(publisher));
        row.put("status", "pending");
        return row;
    }

    private Map<String, Object> pageData(List<Map<String, Object>> list, long total) {
        Map<String, Object> data = new LinkedHashMap<>();
        data.put("list", list);
        data.put("total", total);
        return data;
    }

    private void addIfNotNull(Set<Long> ids, Long id) {
        if (id != null) {
            ids.add(id);
        }
    }

    private Map<Long, User> loadUsers(Set<Long> userIds) {
        if (userIds.isEmpty()) {
            return Map.of();
        }
        return userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity(), (a, b) -> a));
    }

    private Map<Long, Product> loadProducts(Set<Long> productIds) {
        productIds.remove(null);
        if (productIds.isEmpty()) {
            return Map.of();
        }
        return productMapper.selectBatchIds(productIds).stream()
                .collect(Collectors.toMap(Product::getId, Function.identity(), (a, b) -> a));
    }

    private String displayName(User user) {
        if (user == null) {
            return "未知用户";
        }
        return StringUtils.hasText(user.getName()) ? user.getName() : user.getUsername();
    }

    private String categoryName(Integer categoryId) {
        return categoryId == null ? "其他" : PRODUCT_CATEGORIES.getOrDefault(categoryId, "其他");
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
        if (path.startsWith("storage/")) {
            return "/" + path;
        }
        return "/storage/" + path;
    }

    private record PendingItem(LocalDateTime createdAt, Map<String, Object> row) {
    }
}
