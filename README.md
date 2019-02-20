# OpenSearch PHP

版本库 https://github.com/wuding/opensearch-php

依赖库 https://packagist.org/packages/wuding/opensearch

文档 http://www.opensearch.org/ | [English](https://github.com/dewitt/opensearch) | [中文](https://github.com/static-site/tutorial.pub/tree/master/category/Web_search_engine/OpenSearch)



## Install
### 自动安装
```bash
composer require wuding/opensearch
```



### 手动安装
- 你的项目 composer.json 添加自动加载规则：
```json
{
    "autoload": {
        "psr-4": {
            "OpenSearch\\": "vendor/wuding/opensearch-php/src/"
        }
    }
}
```
- 下载 [Zip 包](https://github.com/wuding/opensearch-php/archive/master.zip)并解压到 vendor 目录下
- 你的项目下运行命令：
```sh
composer dump-autoload
```


## Usage

config/opensearch.php

```php
<?php
// 单独定义查询地址便于引用
$search_url = 'https://cpn.red/?q={searchTerms}&client=firefox&src={referrer:source?}&amp;prefix={suggestions:suggestionPrefix?}&amp;index={suggestions:suggestionIndex?}';

return $config = [
    'xmlns' => [
        'ie' => 1,
        'referrer' => 'http://a9.com/-/opensearch/extensions/referrer/1.0/',
    ],
    'short name' => '红券网',
    'description' => '搜索淘宝、天猫优惠券',
    'tags' => 'shopping coupon',
    'contact' => 'contact@urlnk.com',
    'url' => [
        $search_url,
        [
            'https://cpn.red/suggestions?q={searchTerms}&amp;client=firefox', 
            'type' => 'application/x-suggestions+json',
        ],
        [
            'https://cpn.red/opensearch', 
            'rel' => 'self',
            'type' => 'application/opensearchdescription+xml',
        ],
    ],
    'moz: search form' => 'https://cpn.red/',
    'image' => [
        'https://cpn.red/favicon.ico',
        'height' => 16,
        'width' => 16,
        'type' => 'image/x-icon',
    ],
    'input encoding' => 'utf-8',
];
```

opensearch.xml

```php
$config = include APP_PATH . '/config/opensearch.php';
$generate = new \OpenSearch\Generate($config);
return $generate->xml();
```

suggestions.json

```php
$query = $_GET['q'] ?? '';
// 查询数据库搜索建议
$sug = new \Model\SearchSuggestions;
$arr = $sug->list($query);
// 生成 JSON 格式搜索建议列表
$suggestions = new \OpenSearch\Suggestions($arr, $query);
$suggestions->configFile(APP_PATH . '/config/opensearch.php');
// 这里之后会引用 $search_url 生成规范的 Query URLs
return $suggestions->json();
```

