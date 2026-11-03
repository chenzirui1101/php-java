package com.chaoyoung.dto;

import com.fasterxml.jackson.annotation.JsonAlias;
import jakarta.validation.constraints.DecimalMax;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.Max;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ProductRequest {

    @NotBlank(message = "标题不能为空")
    @Size(max = 50, message = "标题不能超过 50 个字符")
    @JsonAlias("name")
    private String title;

    @NotNull(message = "分类不能为空")
    @Min(value = 1, message = "分类不合法")
    @Max(value = 5, message = "分类不合法")
    @JsonAlias("category_id")
    private Integer categoryId;

    @NotNull(message = "价格不能为空")
    @DecimalMin(value = "0.01", message = "价格必须大于 0")
    @DecimalMax(value = "99999999.99", message = "价格超出范围")
    private BigDecimal price;

    /** 库存，创建时可选，默认 0 */
    @Min(value = 0, message = "库存不能为负数")
    private Integer stock;

    @NotBlank(message = "封面图不能为空")
    @JsonAlias({"image", "cover_img"})
    private String coverImg;

    @NotBlank(message = "描述不能为空")
    @Size(max = 500, message = "描述不能超过 500 个字符")
    private String description;
}
