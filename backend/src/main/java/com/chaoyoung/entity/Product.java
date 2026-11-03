package com.chaoyoung.entity;

import com.baomidou.mybatisplus.annotation.IdType;
import com.baomidou.mybatisplus.annotation.TableId;
import com.baomidou.mybatisplus.annotation.TableLogic;
import com.baomidou.mybatisplus.annotation.TableName;
import com.fasterxml.jackson.annotation.JsonFormat;
import com.fasterxml.jackson.annotation.JsonIgnore;
import lombok.Data;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@TableName("products")
public class Product {

    @TableId(value = "id", type = IdType.AUTO)
    private Long id;

    private String title;

    private Integer categoryId;

    /** 价格，对应 decimal(10,2) */
    private BigDecimal price;

    /** 库存，悲观锁扣减的目标字段 */
    private Integer stock;

    private String coverImg;

    private String description;

    private Long userId;

    /** pending / approved / rejected */
    private String status;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime createdAt;

    @JsonFormat(pattern = "yyyy-MM-dd HH:mm:ss")
    private LocalDateTime updatedAt;

    @TableLogic
    @JsonIgnore
    private LocalDateTime deletedAt;
}
