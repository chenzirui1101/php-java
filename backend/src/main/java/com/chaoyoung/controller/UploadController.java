package com.chaoyoung.controller;

import com.chaoyoung.common.Result;
import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.User;
import com.chaoyoung.mapper.UserMapper;
import com.chaoyoung.security.SecurityUtils;
import lombok.RequiredArgsConstructor;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.web.servlet.support.ServletUriComponentsBuilder;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.LinkedHashMap;
import java.util.Map;
import java.util.Set;
import java.util.UUID;

/**
 * 图片上传，对齐 Laravel UploadController：
 * 文件保存到 uploads/images/{用户名}/{时间戳-随机串}.{扩展名}，
 * 对外通过 /storage/** 访问。
 */
@RestController
@RequestMapping("/api/upload")
@RequiredArgsConstructor
public class UploadController {

    private static final long MAX_SIZE = 5 * 1024 * 1024L;
    private static final Set<String> ALLOWED_EXTENSIONS =
            Set.of("jpg", "jpeg", "png", "gif", "webp", "bmp");

    private final UserMapper userMapper;

    @Value("${app.upload-dir:./uploads}")
    private String uploadDir;

    @PostMapping("/image")
    public Result<Map<String, Object>> uploadImage(@RequestParam("file") MultipartFile file) {
        Long userId = SecurityUtils.currentUserId();
        if (userId == null) {
            throw new BusinessException(401, "请先登录");
        }
        if (file == null || file.isEmpty()) {
            throw new BusinessException(422, "请选择要上传的图片");
        }
        if (file.getSize() > MAX_SIZE) {
            throw new BusinessException(422, "图片大小不能超过5MB");
        }
        String contentType = file.getContentType();
        if (contentType == null || !contentType.startsWith("image/")) {
            throw new BusinessException(422, "只能上传图片文件");
        }

        String extension = resolveExtension(file);
        if (extension == null) {
            throw new BusinessException(422, "不支持的图片格式");
        }

        User user = userMapper.selectById(userId);
        String username = user == null ? "user-" + userId : sanitizeUsername(user.getUsername());
        String filename = System.currentTimeMillis() + "-"
                + UUID.randomUUID().toString().replace("-", "").substring(0, 8) + "." + extension;

        Path directory = Paths.get(uploadDir).toAbsolutePath().normalize()
                .resolve("images")
                .resolve(username);
        try {
            Files.createDirectories(directory);
            file.transferTo(directory.resolve(filename).toFile());
        } catch (IOException ex) {
            throw new BusinessException(500, "图片保存失败");
        }

        String relativePath = "images/" + username + "/" + filename;
        String url = ServletUriComponentsBuilder.fromCurrentContextPath()
                .path("/storage/")
                .path(relativePath)
                .toUriString();

        Map<String, Object> data = new LinkedHashMap<>();
        data.put("url", url);
        data.put("path", relativePath);
        data.put("disk", "public");
        return Result.ok("上传成功", data);
    }

    private String resolveExtension(MultipartFile file) {
        String original = file.getOriginalFilename();
        if (StringUtils.hasText(original) && original.lastIndexOf('.') >= 0) {
            String extension = original.substring(original.lastIndexOf('.') + 1).toLowerCase();
            if (ALLOWED_EXTENSIONS.contains(extension)) {
                return extension;
            }
        }
        String contentType = file.getContentType();
        if (contentType == null) {
            return null;
        }
        return switch (contentType) {
            case "image/jpeg", "image/jpg" -> "jpg";
            case "image/png" -> "png";
            case "image/gif" -> "gif";
            case "image/webp" -> "webp";
            case "image/bmp" -> "bmp";
            default -> null;
        };
    }

    private String sanitizeUsername(String username) {
        if (!StringUtils.hasText(username)) {
            return "anonymous";
        }
        return username.replaceAll("[^a-zA-Z0-9_-]", "_");
    }
}
