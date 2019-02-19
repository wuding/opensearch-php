<?php

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
