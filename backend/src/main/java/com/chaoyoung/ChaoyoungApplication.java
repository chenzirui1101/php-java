package com.chaoyoung;

import org.mybatis.spring.annotation.MapperScan;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
@MapperScan("com.chaoyoung.mapper")
public class ChaoyoungApplication {

    public static void main(String[] args) {
        SpringApplication.run(ChaoyoungApplication.class, args);
    }
}
