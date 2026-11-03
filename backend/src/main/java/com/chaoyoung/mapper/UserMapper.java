package com.chaoyoung.mapper;

import com.chaoyoung.entity.User;
import com.baomidou.mybatisplus.core.mapper.BaseMapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Select;

public interface UserMapper extends BaseMapper<User> {

    /**
     * 支持用户名或邮箱登录，与 Laravel AuthController::login 保持一致。
     */
    @Select("SELECT * FROM users WHERE (username = #{account} OR email = #{account}) AND deleted_at IS NULL LIMIT 1")
    User selectByLogin(@Param("account") String account);
}
