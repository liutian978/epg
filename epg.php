<?php
//header("Access-Control-Allow-Origin: *");

// 获取当前的日期，格式为 yyyy-MM-dd
function getCurrentDateInShanghaiTimezone() {
    $dateObj = new DateTime('now', new DateTimeZone('Asia/Shanghai'));
    return $dateObj->format('Y-m-d');
}

function handleRequest($requestUri) {
    // 基础 URL
    //$baseUrl = 'https://51.112114.xyz/';
    $baseUrl = 'https://epg.112114.eu.org/';
    
    // 从查询参数中获取 "ch" 的值，默认为 "CCTV9"
    parse_str(parse_url($requestUri, PHP_URL_QUERY), $queryParams);
    $channel = isset($queryParams['ch']) ? $queryParams['ch'] : '第一财经';
    
    // 设置默认的 date 参数
    if (!isset($queryParams['date'])) {
        $queryParams['date'] = getCurrentDateInShanghaiTimezone();
    }

    // 构建原始 URL
    $originalUrl = $baseUrl . '?' . http_build_query($queryParams);
    
    // 使用 CURL 发送请求并获取响应
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $originalUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // 发送请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // 获取 CURL 错误信息
    if ($response === false) {
        $errorMessage = curl_error($ch);
        curl_close($ch);
        die("CURL error: $errorMessage");
    }

    curl_close($ch);

    // 打印调试信息
    if ($httpCode === 200) {
        // 根据请求 URL 是否包含 ".xml" 设置响应的 Content-Type
        $contentType = strpos($requestUri, '.xml') !== false ? 'text/xml' : 'application/json';
        header("Content-Type: $contentType");
        echo $response;
    } else {
        die("Error fetching the original URL: HTTP Code $httpCode. Response: $response");
    }
}

// 处理请求
handleRequest($_SERVER['REQUEST_URI']);

?>
