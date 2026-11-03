package com.chaoyoung.dto;

import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotNull;
import lombok.Data;

@Data
public class StockDeductRequest {

    @NotNull(message = "扣减数量不能为空")
    @Min(value = 1, message = "扣减数量必须大于 0")
    private Integer quantity;
}
