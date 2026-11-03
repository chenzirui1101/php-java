package com.chaoyoung.controller;

import com.baomidou.mybatisplus.core.conditions.query.LambdaQueryWrapper;
import com.baomidou.mybatisplus.core.conditions.update.LambdaUpdateWrapper;
import com.baomidou.mybatisplus.extension.plugins.pagination.Page;
import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.ForumFavorite;
import com.chaoyoung.entity.ForumLike;
import com.chaoyoung.entity.Reply;
import com.chaoyoung.entity.Topic;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.ForumFavoriteMapper;
import com.chaoyoung.mapper.ForumLikeMapper;
import com.chaoyoung.mapper.ReplyMapper;
import com.chaoyoung.mapper.TopicMapper;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.transaction.annotation.Transactional;
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
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

/**
 * 论坛接口，路由与字段对齐 Laravel ForumController。
 * - GET /api/forum/topics       话题列表（仅 approved，支持分类、排序、分页）
 * - GET /api/forum/hot-topics   热门话题
 * - GET /api/forum/active-users 活跃用户（按 approved 回复数排序）
 */
@RestController
@RequestMapping("/api/forum")
@RequiredArgsConstructor
public class ForumController {

    private static final DateTimeFormatter DATE_TIME = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss");

    private final TopicMapper topicMapper;
    private final ReplyMapper replyMapper;
    private final UserMapper userMapper;
    private final ForumLikeMapper forumLikeMapper;
    private final ForumFavoriteMapper forumFavoriteMapper;

    @GetMapping("/topics")
    public Result<List<Map<String, Object>>> topics(
            @RequestParam(name = "category_id", required = false) String categoryId,
            @RequestParam(name = "sort_by", defaultValue = "new") String sortBy,
            @RequestParam(defaultValue = "1") long page,
            @RequestParam(name = "page_size", defaultValue = "10") long pageSize) {

        long currentPage = Math.max(page, 1);
        long size = Math.max(1, Math.min(pageSize, 50));

        LambdaQueryWrapper<Topic> wrapper = new LambdaQueryWrapper<Topic>()
                .select(Topic::getId, Topic::getTitle, Topic::getContent, Topic::getUserId,
                        Topic::getCategoryId, Topic::getRepliesCount, Topic::getLikesCount,
                        Topic::getFavoritesCount, Topic::getCreatedAt)
                .eq(Topic::getStatus, "approved");

        Long parsedCategoryId = parseCategoryId(categoryId);
        if (parsedCategoryId != null) {
            wrapper.eq(Topic::getCategoryId, parsedCategoryId);
        }
        // 推荐按点赞数，最新按发布时间，与 Laravel 一致
        if ("hot".equals(sortBy)) {
            wrapper.orderByDesc(Topic::getLikesCount);
        }
        wrapper.orderByDesc(Topic::getCreatedAt);

        Page<Topic> pageResult = topicMapper.selectPage(new Page<>(currentPage, size), wrapper);
        Map<Long, User> users = loadUsers(pageResult.getRecords());

        List<Map<String, Object>> list = pageResult.getRecords().stream()
                .map(topic -> toListItem(topic, users.get(topic.getUserId())))
                .collect(Collectors.toList());

        Result<List<Map<String, Object>>> result = Result.ok("success", list);
        result.setTotal(pageResult.getTotal());
        return result;
    }

    @GetMapping("/hot-topics")
    public Result<List<Map<String, Object>>> hotTopics() {
        LambdaQueryWrapper<Topic> wrapper = new LambdaQueryWrapper<Topic>()
                .select(Topic::getId, Topic::getTitle, Topic::getRepliesCount)
                .eq(Topic::getStatus, "approved")
                .orderByDesc(Topic::getRepliesCount)
                .last("LIMIT 5");

        List<Map<String, Object>> data = topicMapper.selectList(wrapper).stream()
                .map(topic -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", topic.getId());
                    row.put("title", topic.getTitle());
                    row.put("replies_count", topic.getRepliesCount());
                    return row;
                })
                .collect(Collectors.toList());
        return Result.ok("success", data);
    }

    @GetMapping("/active-users")
    public Result<List<Map<String, Object>>> activeUsers() {
        List<Reply> replies = replyMapper.selectList(new LambdaQueryWrapper<Reply>()
                .eq(Reply::getStatus, "approved"));

        List<Map.Entry<Long, Long>> ranked = replies.stream()
                .filter(reply -> reply.getUserId() != null)
                .collect(Collectors.groupingBy(Reply::getUserId, Collectors.counting()))
                .entrySet().stream()
                .sorted(Map.Entry.<Long, Long>comparingByValue().reversed())
                .limit(5)
                .toList();

        if (ranked.isEmpty()) {
            return Result.ok("success", List.of());
        }

        Set<Long> userIds = ranked.stream().map(Map.Entry::getKey).collect(Collectors.toSet());
        Map<Long, User> users = userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity()));

        List<Map<String, Object>> data = new ArrayList<>();
        for (Map.Entry<Long, Long> entry : ranked) {
            User user = users.get(entry.getKey());
            if (user == null) {
                continue;
            }
            Map<String, Object> row = new LinkedHashMap<>();
            row.put("id", user.getId());
            row.put("username", user.getUsername());
            row.put("avatar", avatar(user));
            data.add(row);
        }
        return Result.ok("success", data);
    }

    @PostMapping("/topics")
    public Result<Map<String, Object>> createTopic(@RequestBody(required = false) Map<String, Object> body) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        if (body == null) {
            throw new BusinessException(422, "请求参数不能为空");
        }

        String title = body.get("title") == null ? null : String.valueOf(body.get("title")).trim();
        String content = body.get("content") == null ? null : String.valueOf(body.get("content"));
        Long categoryId = toLong(body.get("category_id"));

        if (!StringUtils.hasText(title)) {
            throw new BusinessException(422, "标题不能为空");
        }
        if (title.length() > 100) {
            throw new BusinessException(422, "标题不能超过100个字符");
        }
        if (!StringUtils.hasText(content)) {
            throw new BusinessException(422, "内容不能为空");
        }
        if (categoryId != null && (categoryId < 1 || categoryId > 5)) {
            throw new BusinessException(422, "分类不存在");
        }

        Topic topic = new Topic();
        topic.setTitle(title);
        topic.setContent(content.trim());
        topic.setCategoryId(categoryId);
        topic.setUserId(userId);
        topic.setStatus("pending");
        topic.setRepliesCount(0);
        topic.setLikesCount(0);
        topic.setFavoritesCount(0);
        topic.setCreatedAt(LocalDateTime.now());
        topic.setUpdatedAt(LocalDateTime.now());
        topicMapper.insert(topic);

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("id", topic.getId());
        data.put("title", topic.getTitle());
        data.put("content", topic.getContent());
        data.put("category_id", topic.getCategoryId());
        data.put("author", author(userId, userMapper.selectById(userId)));
        data.put("created_at", topic.getCreatedAt().format(DATE_TIME));
        data.put("replies_count", 0);
        data.put("likes_count", 0);
        data.put("status", topic.getStatus());
        data.put("statusText", statusText(topic.getStatus()));
        return Result.ok("话题创建成功，等待管理员审核", data);
    }

    @GetMapping("/topics/{id}")
    public Result<Map<String, Object>> topicDetail(@PathVariable Long id) {
        Topic topic = topicMapper.selectOne(new LambdaQueryWrapper<Topic>()
                .eq(Topic::getId, id)
                .eq(Topic::getStatus, "approved"));
        if (topic == null) {
            throw new BusinessException(404, "话题不存在");
        }
        User user = topic.getUserId() == null ? null : userMapper.selectById(topic.getUserId());
        return Result.ok("success", toListItem(topic, user));
    }

    @GetMapping("/topics/{id}/replies")
    public Result<List<Map<String, Object>>> topicReplies(@PathVariable Long id) {
        List<Reply> replies = replyMapper.selectList(new LambdaQueryWrapper<Reply>()
                .eq(Reply::getTopicId, id)
                .eq(Reply::getStatus, "approved")
                .orderByDesc(Reply::getCreatedAt));

        Set<Long> userIds = replies.stream()
                .map(Reply::getUserId)
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());
        Map<Long, User> users = userIds.isEmpty()
                ? Map.of()
                : userMapper.selectBatchIds(userIds).stream()
                        .collect(Collectors.toMap(User::getId, Function.identity()));

        List<Map<String, Object>> data = replies.stream()
                .map(reply -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", reply.getId());
                    row.put("user", author(reply.getUserId(), users.get(reply.getUserId())));
                    row.put("content", reply.getContent());
                    row.put("created_at", reply.getCreatedAt() == null ? "" : reply.getCreatedAt().format(DATE_TIME));
                    return row;
                })
                .collect(Collectors.toList());
        return Result.ok("success", data);
    }

    @PostMapping("/topics/{id}/replies")
    public Result<Map<String, Object>> addReply(@PathVariable Long id,
                                                @RequestBody(required = false) Map<String, String> body) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        Topic topic = topicMapper.selectById(id);
        if (topic == null) {
            throw new BusinessException(404, "话题不存在");
        }
        String content = body == null ? null : body.get("content");
        if (!StringUtils.hasText(content)) {
            throw new BusinessException(422, "评论内容不能为空");
        }

        Reply reply = new Reply();
        reply.setTopicId(id);
        reply.setUserId(userId);
        reply.setContent(content.trim());
        reply.setStatus("pending");
        reply.setCreatedAt(LocalDateTime.now());
        reply.setUpdatedAt(LocalDateTime.now());
        replyMapper.insert(reply);

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("id", reply.getId());
        data.put("user", author(userId, userMapper.selectById(userId)));
        data.put("content", reply.getContent());
        data.put("created_at", reply.getCreatedAt().format(DATE_TIME));
        data.put("status", reply.getStatus());
        data.put("statusText", statusText(reply.getStatus()));
        return Result.ok("评论添加成功，等待管理员审核", data);
    }

    @GetMapping("/recommendations")
    public Result<List<Map<String, Object>>> recommendations() {
        LambdaQueryWrapper<Topic> wrapper = new LambdaQueryWrapper<Topic>()
                .select(Topic::getId, Topic::getTitle, Topic::getUserId, Topic::getRepliesCount,
                        Topic::getLikesCount, Topic::getCreatedAt)
                .eq(Topic::getStatus, "approved")
                .orderByDesc(Topic::getLikesCount)
                .last("LIMIT 5");
        List<Topic> topics = topicMapper.selectList(wrapper);
        Map<Long, User> users = loadUsers(topics);

        List<Map<String, Object>> data = topics.stream()
                .map(topic -> {
                    User user = users.get(topic.getUserId());
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", topic.getId());
                    row.put("title", topic.getTitle());
                    row.put("author_username", user == null ? "匿名用户" : user.getUsername());
                    row.put("created_at", topic.getCreatedAt() == null ? "" : topic.getCreatedAt().format(DATE_TIME));
                    row.put("reads_count", 0);
                    row.put("replies_count", topic.getRepliesCount() == null ? 0 : topic.getRepliesCount());
                    return row;
                })
                .collect(Collectors.toList());
        return Result.ok("success", data);
    }

    @GetMapping("/topics/{id}/navigation")
    public Result<Map<String, Object>> topicNavigation(@PathVariable Long id) {
        Topic current = topicMapper.selectById(id);
        if (current == null) {
            throw new BusinessException(404, "话题不存在");
        }

        LambdaQueryWrapper<Topic> wrapper = new LambdaQueryWrapper<Topic>()
                .select(Topic::getId, Topic::getTitle)
                .eq(Topic::getStatus, "approved");
        if (current.getCategoryId() == null) {
            wrapper.isNull(Topic::getCategoryId);
        } else {
            wrapper.eq(Topic::getCategoryId, current.getCategoryId());
        }
        wrapper.orderByAsc(Topic::getId);

        List<Topic> topics = topicMapper.selectList(wrapper);
        int index = -1;
        for (int i = 0; i < topics.size(); i++) {
            if (topics.get(i).getId().equals(id)) {
                index = i;
                break;
            }
        }

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("prev", index > 0 ? topicBrief(topics.get(index - 1)) : null);
        data.put("next", index >= 0 && index < topics.size() - 1 ? topicBrief(topics.get(index + 1)) : null);
        return Result.ok("success", data);
    }

    @GetMapping("/topics/{id}/user-status")
    public Result<Map<String, Object>> userTopicStatus(@PathVariable Long id) {
        Long userId = SecurityUtils.currentUserId();
        Map<String, Object> data = new LinkedHashMap<>();
        if (userId == null) {
            data.put("is_liked", false);
            data.put("is_favorited", false);
            return Result.ok("success", data);
        }
        data.put("is_liked", forumLikeMapper.selectCount(likeQuery(userId, id)) > 0);
        data.put("is_favorited", forumFavoriteMapper.selectCount(favoriteQuery(userId, id)) > 0);
        return Result.ok("success", data);
    }

    @PostMapping("/topics/{id}/like")
    @Transactional(rollbackFor = Exception.class)
    public Result<Map<String, Object>> toggleLike(@PathVariable Long id) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        Topic topic = topicMapper.selectById(id);
        if (topic == null) {
            throw new BusinessException(404, "话题不存在");
        }

        boolean liked;
        if (forumLikeMapper.selectCount(likeQuery(userId, id)) > 0) {
            forumLikeMapper.delete(likeQuery(userId, id));
            changeTopicCounter(id, "likes_count", -1);
            liked = false;
        } else {
            ForumLike like = new ForumLike();
            like.setUserId(userId);
            like.setTopicId(id);
            like.setCreatedAt(LocalDateTime.now());
            like.setUpdatedAt(LocalDateTime.now());
            forumLikeMapper.insert(like);
            changeTopicCounter(id, "likes_count", 1);
            liked = true;
        }

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("isLiked", liked);
        return Result.ok(liked ? "点赞成功" : "已取消点赞", data);
    }

    @PostMapping("/topics/{id}/favorite")
    @Transactional(rollbackFor = Exception.class)
    public Result<Map<String, Object>> toggleFavorite(@PathVariable Long id) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        Topic topic = topicMapper.selectById(id);
        if (topic == null) {
            throw new BusinessException(404, "话题不存在");
        }

        boolean favorited;
        if (forumFavoriteMapper.selectCount(favoriteQuery(userId, id)) > 0) {
            forumFavoriteMapper.delete(favoriteQuery(userId, id));
            changeTopicCounter(id, "favorites_count", -1);
            favorited = false;
        } else {
            ForumFavorite favorite = new ForumFavorite();
            favorite.setUserId(userId);
            favorite.setTopicId(id);
            favorite.setCreatedAt(LocalDateTime.now());
            favorite.setUpdatedAt(LocalDateTime.now());
            forumFavoriteMapper.insert(favorite);
            changeTopicCounter(id, "favorites_count", 1);
            favorited = true;
        }

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("isFavorited", favorited);
        return Result.ok(favorited ? "收藏成功" : "已取消收藏", data);
    }

    @GetMapping("/favorites")
    public Result<List<Map<String, Object>>> myFavorites() {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }

        List<ForumFavorite> favorites = forumFavoriteMapper.selectList(
                new LambdaQueryWrapper<ForumFavorite>()
                        .eq(ForumFavorite::getUserId, userId)
                        .orderByDesc(ForumFavorite::getCreatedAt));
        if (favorites.isEmpty()) {
            return Result.ok("success", List.of());
        }

        Set<Long> topicIds = favorites.stream().map(ForumFavorite::getTopicId).collect(Collectors.toSet());
        Map<Long, Topic> topics = topicMapper.selectList(new LambdaQueryWrapper<Topic>()
                        .in(Topic::getId, topicIds)
                        .eq(Topic::getStatus, "approved"))
                .stream()
                .collect(Collectors.toMap(Topic::getId, Function.identity()));

        List<Map<String, Object>> data = favorites.stream()
                .map(favorite -> topics.get(favorite.getTopicId()))
                .filter(Objects::nonNull)
                .map(topic -> {
                    Map<String, Object> row = new LinkedHashMap<>();
                    row.put("id", topic.getId());
                    row.put("title", topic.getTitle());
                    row.put("content", topic.getContent());
                    row.put("category_id", topic.getCategoryId());
                    row.put("user_id", topic.getUserId());
                    row.put("created_at", topic.getCreatedAt() == null ? "" : topic.getCreatedAt().format(DATE_TIME));
                    row.put("replies_count", topic.getRepliesCount() == null ? 0 : topic.getRepliesCount());
                    row.put("likes_count", topic.getLikesCount() == null ? 0 : topic.getLikesCount());
                    return row;
                })
                .collect(Collectors.toList());
        return Result.ok("success", data);
    }

    private LambdaQueryWrapper<ForumLike> likeQuery(Long userId, Long topicId) {
        return new LambdaQueryWrapper<ForumLike>()
                .eq(ForumLike::getUserId, userId)
                .eq(ForumLike::getTopicId, topicId);
    }

    private LambdaQueryWrapper<ForumFavorite> favoriteQuery(Long userId, Long topicId) {
        return new LambdaQueryWrapper<ForumFavorite>()
                .eq(ForumFavorite::getUserId, userId)
                .eq(ForumFavorite::getTopicId, topicId);
    }

    /**
     * 计数器只允许加减 1，且减到 0 为止，避免出现负数。
     */
    private void changeTopicCounter(Long topicId, String column, int delta) {
        String sql = "CASE WHEN " + column + " > 0 THEN " + column + " " + (delta > 0 ? "+" : "-")
                + " 1 ELSE 0 END";
        topicMapper.update(null, new LambdaUpdateWrapper<Topic>()
                .eq(Topic::getId, topicId)
                .setSql(column + " = " + sql));
    }

    private Map<String, Object> topicBrief(Topic topic) {
        Map<String, Object> row = new LinkedHashMap<>();
        row.put("id", topic.getId());
        row.put("title", topic.getTitle());
        return row;
    }

    private String statusText(String status) {
        return switch (status == null ? "" : status) {
            case "pending" -> "待审核";
            case "approved" -> "已通过";
            case "rejected" -> "已拒绝";
            default -> "未知";
        };
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

    private Long parseCategoryId(String categoryId) {
        if (!StringUtils.hasText(categoryId) || "all".equals(categoryId) || "0".equals(categoryId)) {
            return null;
        }
        try {
            return Long.valueOf(categoryId);
        } catch (NumberFormatException e) {
            return null;
        }
    }

    private Map<Long, User> loadUsers(List<Topic> topics) {
        Set<Long> userIds = topics.stream()
                .map(Topic::getUserId)
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());
        if (userIds.isEmpty()) {
            return Map.of();
        }
        return userMapper.selectBatchIds(userIds).stream()
                .collect(Collectors.toMap(User::getId, Function.identity()));
    }

    private Map<String, Object> toListItem(Topic topic, User user) {
        Map<String, Object> row = new LinkedHashMap<>();
        row.put("id", topic.getId());
        row.put("title", topic.getTitle());
        row.put("content", topic.getContent());
        row.put("category_id", topic.getCategoryId());
        row.put("author", author(topic.getUserId(), user));
        row.put("created_at", topic.getCreatedAt() == null ? "" : topic.getCreatedAt().format(DATE_TIME));
        row.put("replies_count", topic.getRepliesCount() == null ? 0 : topic.getRepliesCount());
        row.put("likes_count", topic.getLikesCount() == null ? 0 : topic.getLikesCount());
        row.put("favorites_count", topic.getFavoritesCount() == null ? 0 : topic.getFavoritesCount());
        return row;
    }

    private Map<String, Object> author(Long userId, User user) {
        Map<String, Object> author = new LinkedHashMap<>();
        if (user == null) {
            author.put("id", userId);
            author.put("username", "匿名用户");
            author.put("avatar", fallbackAvatar(userId));
            return author;
        }
        author.put("id", user.getId());
        author.put("username", user.getUsername());
        author.put("avatar", avatar(user));
        return author;
    }

    private String avatar(User user) {
        return StringUtils.hasText(user.getAvatar()) ? user.getAvatar() : fallbackAvatar(user.getId());
    }

    private String fallbackAvatar(Long userId) {
        return "https://picsum.photos/100/100?random=" + (userId == null ? 1 : userId);
    }
}
