package com.chaoyoung.mapper;

import com.chaoyoung.entity.Product;
import com.baomidou.mybatisplus.core.mapper.BaseMapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;
import org.apache.ibatis.annotations.Update;

public interface ProductMapper extends BaseMapper<Product> {

    /**
     * 悲观锁查询：事务内锁定该商品行，阻止其他事务同时读取并修改库存。
     * 必须在 @Transactional 中调用，否则 FOR UPDATE 的锁会在语句结束后立即释放。
     */
    @Select("SELECT * FROM products WHERE id = #{id} AND deleted_at IS NULL FOR UPDATE")
    Product selectByIdForUpdate(@Param("id") Long id);

    /**
     * 扣减库存。WHERE 中再次带 stock >= quantity 作为兜底，防止绕过悲观锁的调用方式。
     */
    @Update("UPDATE products SET stock = stock - #{quantity}, updated_at = CURRENT_TIMESTAMP " +
            "WHERE id = #{id} AND deleted_at IS NULL AND stock >= #{quantity}")
    int decreaseStock(@Param("id") Long id, @Param("quantity") Integer quantity);

    /**
     * 扣减后回写缓存版本号（Redis 缓存配合见 ProductStockService 注释）。
     */
    @Update("UPDATE products SET updated_at = CURRENT_TIMESTAMP WHERE id = #{id}")
    int touchUpdatedAt(@Param("id") Long id);
}
