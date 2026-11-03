package com.chaoyoung.entity;

import com.baomidou.mybatisplus.annotation.IdType;
import com.baomidou.mybatisplus.annotation.TableId;
import com.baomidou.mybatisplus.annotation.TableName;
import com.fasterxml.jackson.annotation.JsonFormat;
import com.fasterxml.jackson.databind.PropertyNamingStrategies;
import com.fasterxml.jackson.databind.annotation.JsonNaming;
import lombok.Data;

import java.time.LocalDateTime;

/**
 * 校园活动，字段对齐 Laravel activities 表。
 * Laravel 直接返回模型（蛇形命名），这里用 SnakeCase 策略保持前端字段一致。
 */
@Data
@TableName("activities")
@JsonNaming(PropertyNamingStrategies.SnakeCaseStrategy.class)
public class Activity {

    @TableId(value = "id", type = IdType.AUTO)
    private Long id;

    private String title;

    private String poster;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime startTime;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime endTime;

    private String description;

    private Long categoryId;

    private Long userId;

    /** pending / approved / rejected */
    private String moderationStatus;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime moderatedAt;

    private Long moderatedBy;

    private String rejectReason;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime createdAt;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime updatedAt;
}
