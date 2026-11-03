package com.chaoyoung.filter;

import com.chaoyoung.common.util.SanitizerUtils;
import jakarta.servlet.ReadListener;
import jakarta.servlet.ServletInputStream;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletRequestWrapper;

import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.nio.charset.StandardCharsets;
import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.Map;

/**
 * 可重复读取且已清洗参数的请求包装器。
 * JSON body 清洗后缓存在内存中，供 @RequestBody 与防篡改拦截器共同使用。
 */
public class SanitizedRequestWrapper extends HttpServletRequestWrapper {

    public static final String CACHED_BODY_ATTRIBUTE = "CHAOYOUNG_CACHED_BODY";

    private final byte[] body;
    private final Map<String, String[]> parameters;

    public SanitizedRequestWrapper(HttpServletRequest request, byte[] body) {
        super(request);
        this.body = body;
        this.parameters = sanitizeParameters(super.getParameterMap());
        if (body != null) {
            setAttribute(CACHED_BODY_ATTRIBUTE, new String(body, StandardCharsets.UTF_8));
        }
    }

    private Map<String, String[]> sanitizeParameters(Map<String, String[]> source) {
        Map<String, String[]> result = new LinkedHashMap<>();
        source.forEach((key, values) -> {
            String[] sanitized = new String[values.length];
            for (int i = 0; i < values.length; i++) {
                sanitized[i] = SanitizerUtils.sanitize(key, values[i]);
            }
            result.put(key, sanitized);
        });
        return result;
    }

    @Override
    public String getParameter(String name) {
        String[] values = parameters.get(name);
        return values == null || values.length == 0 ? null : values[0];
    }

    @Override
    public String[] getParameterValues(String name) {
        return parameters.get(name);
    }

    @Override
    public Map<String, String[]> getParameterMap() {
        return Collections.unmodifiableMap(parameters);
    }

    @Override
    public ServletInputStream getInputStream() throws IOException {
        return body != null ? new CachedServletInputStream(body) : super.getInputStream();
    }

    @Override
    public BufferedReader getReader() throws IOException {
        if (body == null) {
            return super.getReader();
        }
        return new BufferedReader(new InputStreamReader(new ByteArrayInputStream(body), StandardCharsets.UTF_8));
    }

    @Override
    public int getContentLength() {
        return body != null ? body.length : super.getContentLength();
    }

    @Override
    public long getContentLengthLong() {
        return body != null ? body.length : super.getContentLengthLong();
    }

    /**
     * 允许重复读取缓存的请求体。
     */
    private static class CachedServletInputStream extends ServletInputStream {

        private final ByteArrayInputStream inputStream;

        CachedServletInputStream(byte[] body) {
            this.inputStream = new ByteArrayInputStream(body);
        }

        @Override
        public int read() throws IOException {
            return inputStream.read();
        }

        @Override
        public boolean isFinished() {
            return inputStream.available() == 0;
        }

        @Override
        public boolean isReady() {
            return true;
        }

        @Override
        public void setReadListener(ReadListener readListener) {
            throw new UnsupportedOperationException("同步过滤器不支持异步读取");
        }
    }
}
