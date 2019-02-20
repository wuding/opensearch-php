<?php

namespace OpenSearch;

function get_config_var($name = null)
{
    $name = $name ? : 'config';
    $url_scheme = $_SERVER['REQUEST_SCHEME'];
    $url_host = $_SERVER['HTTP_HOST'];
    
    $search_host = "$url_scheme://$url_host";
    $query_str = "q={searchTerms}&client=firefox&src={referrer:source}";
    $search_url = "$search_host/?$query_str";
    $query_url = $search_url . "&amp;prefix={suggestions:suggestionPrefix}&amp;index={suggestions:suggestionIndex?}";

    $config = [
        'xmlns' => [
            'ie' => 1,
            'referrer' => 'http://a9.com/-/opensearch/extensions/referrer/1.0/',
        ],
        'short name' => '红券网',
        'description' => '搜索淘宝、天猫优惠券',
        'tags' => 'shopping coupon taobao tmall',
        'contact' => 'contact@urlnk.com',
        'url' => [
            $search_url,
            [
                "$search_host/suggestions?$query_str", 
                'type' => 'application/x-suggestions+json',
            ],
            [
                "$search_host/opensearch?rel=self", 
                'rel' => 'self',
                'type' => 'application/opensearchdescription+xml',
            ],
        ],
        'moz: search form' => $search_host,
        'image' => [
            "$search_host/favicon.ico",
            'height' => 16,
            'width' => 16,
            'type' => 'image/x-icon',
        ],
        'input encoding' => 'utf-8',
    ];

    return $$name;
}

return get_config_var();
