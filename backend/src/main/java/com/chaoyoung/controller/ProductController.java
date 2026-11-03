package com.chaoyoung.controller;

import com.chaoyoung.annotation.ContentModeration;
import com.chaoyoung.common.Result;
import com.chaoyoung.dto.ProductRequest;
import com.chaoyoung.dto.StockDeductRequest;
import com.chaoyoung.entity.Product;
import com.chaoyoung.service.ProductService;
import com.chaoyoung.service.ProductStockService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.Map;

/**
 * 商品接口，路径与 Laravel ProductController 对齐：/api/products/*
 */
@RestController
@RequestMapping("/api/products")
@RequiredArgsConstructor
public class ProductController {

    private final ProductService productService;
    private final ProductStockService productStockService;

    @GetMapping
    public Result<Map<String, Object>> index(@RequestParam(defaultValue = "1") int pageNum,
                                             @RequestParam(defaultValue = "12") int pageSize,
                                             @RequestParam(defaultValue = "0") int categoryId,
                                             @RequestParam(defaultValue = "") String keyword) {
        return Result.ok(productService.list(pageNum, pageSize, categoryId, keyword));
    }

    @GetMapping("/my/list")
    public Result<Map<String, Object>> myProducts() {
        return Result.ok(productService.myProducts());
    }

    @GetMapping("/{id}")
    public Result<Map<String, Object>> show(@PathVariable Long id) {
        return Result.ok(productService.detail(id));
    }

    @PostMapping
    @ContentModeration(type = "product", fields = {"title", "description"})
    public Result<Product> store(@Valid @RequestBody ProductRequest request) {
        return Result.ok("商品发布成功，等待管理员审核", productService.create(request));
    }

    @PutMapping("/{id}")
    @ContentModeration(type = "product", fields = {"title", "description"})
    public Result<Product> update(@PathVariable Long id, @Valid @RequestBody ProductRequest request) {
        return Result.ok("商品更新成功，等待管理员审核", productService.update(id, request));
    }

    @DeleteMapping("/{id}")
    public Result<Void> destroy(@PathVariable Long id) {
        productService.delete(id);
        return Result.ok("商品删除成功", null);
    }

    /**
     * 悲观锁扣减库存。需要登录且携带防篡改签名（POST 写请求）。
     */
    @PostMapping("/{id}/stock/deduct")
    public Result<Map<String, Object>> deductStock(@PathVariable Long id,
                                                   @Valid @RequestBody StockDeductRequest request) {
        return Result.ok("库存扣减成功", productStockService.deductStock(id, request.getQuantity()));
    }
}
