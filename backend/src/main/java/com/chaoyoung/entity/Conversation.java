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
@TableName("conversations")
public class Conversation {

    @TableId(value = "id", type = IdType.AUTO)
    private Long id;

    private Long productId;

    /** 发起会话的买家 */
    private Long userId;

    /** 卖家 */
    private Long sellerId;

    private String lastMessage;

    private LocalDateTime lastMessageTime;

    private Integer unreadCount;

    /** product / lost_found / forum 等 */
    private String resourceType;

    private Long resourceId;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime createdAt;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime updatedAt;

    @TableLogic
    @JsonIgnore
    private LocalDateTime deletedAt;
}
