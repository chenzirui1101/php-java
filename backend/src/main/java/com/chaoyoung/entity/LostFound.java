package com.chaoyoung.entity;

import com.baomidou.mybatisplus.annotation.IdType;
import com.baomidou.mybatisplus.annotation.TableId;
import com.baomidou.mybatisplus.annotation.TableLogic;
import com.baomidou.mybatisplus.annotation.TableName;
import com.fasterxml.jackson.annotation.JsonFormat;
import com.fasterxml.jackson.annotation.JsonIgnore;
import lombok.Data;

import java.time.LocalDateTime;

@Data
@TableName("lost_found")
public class LostFound {

    @TableId(value = "id", type = IdType.AUTO)
    private Long id;

    private String title;

    /** lost 寻物启事 / found 失物招领 */
    private String type;

    /** looking 寻找中 / found 已找到 */
    private String status;

    private String location;

    private String coverImg;

    private String description;

    private Long userId;

    /** pending / approved / rejected */
    private String moderationStatus;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime createdAt;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime updatedAt;

    @TableLogic
    @JsonIgnore
    private LocalDateTime deletedAt;
}
