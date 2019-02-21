<?php

$query = $_GET['q'] ?? '';
// 查询数据库搜索建议
$Terms = new \app\model\SearchTerms;
$arr = $Terms->view($query);
// 生成 JSON 格式搜索建议列表
$suggestions = new \OpenSearch\Suggestions($arr, $query);
$suggestions->configFile(dirname(__FILE__) . '/config/opensearch.php');
// 这里之后会引用 $search_url 生成规范的 Query URLs
echo $suggestions->json();
