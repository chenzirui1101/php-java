package com.chaoyoung.service;

import jakarta.annotation.PostConstruct;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Set;

/**
 * 内容审核服务。
 * 当前为本地敏感词模拟实现；真实场景可在此处接入内容安全/AI 审核接口，保持调用方不变。
 */
@Slf4j
@Service
public class ContentModerationService {

    @Value("${app.moderation.sensitive-words:}")
    private String sensitiveWordsConfig;

    private Set<String> sensitiveWords = Set.of();

    @PostConstruct
    public void init() {
        if (StringUtils.hasText(sensitiveWordsConfig)) {
            sensitiveWords = Arrays.stream(sensitiveWordsConfig.split(","))
                    .map(String::trim)
                    .filter(StringUtils::hasText)
                    .collect(LinkedHashSet::new, Set::add, Set::addAll);
        }
    }

    /**
     * @return 违规原因列表，空列表表示审核通过
     */
    public List<String> check(String text, String type) {
        List<String> reasons = new ArrayList<>();
        if (!StringUtils.hasText(text)) {
            return reasons;
        }
        for (String word : sensitiveWords) {
            if (text.contains(word)) {
                reasons.add("内容包含敏感词：" + word);
            }
        }
        // TODO 生产环境替换为 AI/内容安全接口：调用外部服务并传入 type、text
        if (!reasons.isEmpty()) {
            log.info("内容审核未通过，type={}，reasons={}", type, reasons);
        }
        return reasons;
    }
}
