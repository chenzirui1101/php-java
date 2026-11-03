package com.chaoyoung.common.util;

import java.util.Locale;
import java.util.Set;
import java.util.regex.Pattern;

/**
 * XSS / SQL 注入清洗工具。
 * 说明：MyBatis 参数绑定已能防止绝大多数 SQL 注入，此处作为纵深防御的请求入口清洗。
 */
public final class SanitizerUtils {

    private static final Set<String> SENSITIVE_KEYS = Set.of(
            "password", "oldpassword", "newpassword", "confirmpassword",
            "token", "accesstoken", "refreshtoken", "secret");

    private static final Pattern SCRIPT_PATTERN =
            Pattern.compile("(?is)<script.*?>.*?</script>");

    private static final Pattern STYLE_PATTERN =
            Pattern.compile("(?is)<style.*?>.*?</style>");

    private static final Pattern SQL_INJECTION_PATTERN =
            Pattern.compile("(?is)(\\bunion\\s+(all\\s+)?select\\b|\\bselect\\s+\\w+.*?\\bfrom\\b|"
                    + "\\binsert\\s+into\\b|\\bdelete\\s+from\\b|\\bdrop\\s+(table|database)\\b|"
                    + "\\btruncate\\s+table\\b|\\bupdate\\s+\\w+\\s+set\\b|\\bexec(ute)?\\b|"
                    + "\\bxp_cmdshell\\b|;\\s*(select|insert|update|delete|drop|truncate)|\\s--\\s?|"
                    + "\\b(and|or)\\s+\\d+\\s*=\\s*\\d+)");

    private static final Pattern PATH_TRAVERSAL_PATTERN = Pattern.compile("(?i)(\\.\\./|\\.\\.\\\\)");

    private static final Pattern INVISIBLE_PATTERN = Pattern.compile("[\\p{Cntrl}&&[^\\r\\n\\t]]");

    private SanitizerUtils() {
    }

    public static boolean isSensitiveKey(String key) {
        if (key == null) {
            return false;
        }
        String normalized = key.toLowerCase(Locale.ROOT).replace("_", "").replace("-", "");
        return SENSITIVE_KEYS.contains(normalized);
    }

    /**
     * 按字段名清洗：密码/token 等字段不做 HTML 转义，避免影响原有值。
     */
    public static String sanitize(String key, String value) {
        if (value == null) {
            return null;
        }
        if (isSensitiveKey(key)) {
            return INVISIBLE_PATTERN.matcher(value).replaceAll("");
        }
        return sanitize(value);
    }

    public static String sanitize(String value) {
        if (value == null) {
            return null;
        }
        String cleaned = value.replace("\u0000", "");
        cleaned = SCRIPT_PATTERN.matcher(cleaned).replaceAll("");
        cleaned = STYLE_PATTERN.matcher(cleaned).replaceAll("");
        cleaned = SQL_INJECTION_PATTERN.matcher(cleaned).replaceAll("");
        cleaned = PATH_TRAVERSAL_PATTERN.matcher(cleaned).replaceAll("");
        cleaned = INVISIBLE_PATTERN.matcher(cleaned).replaceAll("");
        // HTML 转义，阻断存储型 XSS
        cleaned = cleaned.replace("<", "&lt;").replace(">", "&gt;");
        return cleaned;
    }
}
