package com.chaoyoung.service;

import com.chaoyoung.common.exception.BusinessException;
import com.chaoyoung.entity.Product;
import com.chaoyoung.mapper.ProductMapper;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.data.redis.core.StringRedisTemplate;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.transaction.support.TransactionSynchronization;
import org.springframework.transaction.support.TransactionSynchronizationManager;

import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * 高并发扣减库存（悲观锁方案）。
 *
 * <p>Redis 缓存如何配合此流程：</p>
 * <ol>
 *   <li>读多写少场景下，库存可缓存在 Redis（key：product:stock:{id}，TTL 30-60s），读请求先读缓存，
 *       未命中再回源 MySQL，降低数据库读压力。</li>
 *   <li>缓存只做加速，不是扣减依据。可在扣减前用 Redis Lua 脚本做原子预扣（DECRBY），返回值小于 0
 *       直接快速失败，避免无效流量打到数据库；预扣成功后如果事务失败，需在异常路径用 INCRBY 补偿。</li>
 *   <li>权威库存始终以 MySQL 为准：本方法在事务内执行 SELECT ... FOR UPDATE 锁定商品行，
 *       再用 stock >= quantity 条件更新，确保并发下不超卖。</li>
 *   <li>事务提交后（afterCommit）删除 product:stock:{id} 缓存，让下一次读取回源到最新库存。
 *       不要在提交前删除，否则并发读可能把旧值重新写回缓存。</li>
 *   <li>缓存要设置 TTL 并缓存空值，防止穿透和雪崩；热点商品可加本地缓存，但扣减前必须回源或走 Redis 预扣。</li>
 * </ol>
 */
@Slf4j
@Service
@RequiredArgsConstructor
public class ProductStockService {

    private static final String STOCK_CACHE_KEY_PREFIX = "chaoyoung:product:stock:";

    private final ProductMapper productMapper;
    private final StringRedisTemplate redisTemplate;

    @Transactional(rollbackFor = Exception.class)
    public Map<String, Object> deductStock(Long productId, Integer quantity) {
        if (quantity == null || quantity <= 0) {
            throw new BusinessException(422, "验证失败", Map.of("quantity", List.of("扣减数量必须大于 0")));
        }

        // 悲观锁：锁定商品行，事务提交前其他扣减事务会阻塞等待
        Product product = productMapper.selectByIdForUpdate(productId);
        if (product == null) {
            throw new BusinessException(404, "商品不存在");
        }

        int currentStock = product.getStock() == null ? 0 : product.getStock();
        if (currentStock < quantity) {
            throw new BusinessException(400, "库存不足",
                    Map.of("stock", List.of("当前库存 " + currentStock + "，无法扣减 " + quantity)));
        }

        int affected = productMapper.decreaseStock(productId, quantity);
        if (affected == 0) {
            throw new BusinessException(409, "库存扣减失败，请重试");
        }

        // 事务提交成功后再失效 Redis 库存缓存
        evictStockCacheAfterCommit(productId);

        Map<String, Object> result = new LinkedHashMap<>();
        result.put("productId", productId);
        result.put("deducted", quantity);
        result.put("remaining", currentStock - quantity);
        return result;
    }

    private void evictStockCacheAfterCommit(Long productId) {
        if (TransactionSynchronizationManager.isSynchronizationActive()) {
            TransactionSynchronizationManager.registerSynchronization(new TransactionSynchronization() {
                @Override
                public void afterCommit() {
                    deleteStockCache(productId);
                }
            });
        } else {
            deleteStockCache(productId);
        }
    }

    private void deleteStockCache(Long productId) {
        try {
            redisTemplate.delete(STOCK_CACHE_KEY_PREFIX + productId);
        } catch (RuntimeException e) {
            log.warn("删除商品库存缓存失败，productId={}: {}", productId, e.getMessage());
        }
    }
}
