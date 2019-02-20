<?php
namespace OpenSearch;

class Description
{
    public $config = [];
    public $namespace = [
        'suggestions' => 'http://www.opensearch.org/specifications/opensearch/extensions/suggestions/1.1',
        'referrer' => 'http://a9.com/-/opensearch/extensions/referrer/1.0/',
        'moz' => 'http://www.mozilla.org/2006/browser/search/',
        'ie' => 'http://schemas.microsoft.com/Search/2008/',
    ];
    public $ns = [];
    public $xmlns = [];
    public $shortName = null;
    public $description = null;
    public $tags = null;
    public $contact = null;
    public $url = [];
    public $mozSearchForm = null;
    public $image = [];

    public function __construct($config = null)
    {
        $this->init($config);
    }

    public function init($config = null)
    {
        if (!$config) {
            return false;
        }

        if (is_string($config)) {
            $config = include $config;
        }
        $this->config = $config;

        foreach ($config as $key => $value) {
            $item = preg_replace('/\s+/', '', ucwords($key));
            $func = lcfirst($item);
            if (preg_match('/^([a-z]+):/i', $item, $matches)) {
                $k = explode(':', $key)[0]; #print_r([$k, $matches]);
                $this->ns[$matches[1]] = $k;
                if (array_key_exists($k, $this->namespace)) {
                    $this->xmlns = array_merge([$k => $this->namespace[$k]], $this->xmlns);
                }
            }
            $func = preg_replace('/:/', '', $func);
            if (in_array($func, get_class_methods($this))) {
                $this->$func($value);
            } else {
                $this->$func = $value;
            }
            
        }
    }

    public function xmlns($variable)
    {
        if (is_string($variable)) {
            $variable = explode(',', $variable);
        }
        

        foreach ($variable as $key => $value) {
            $value = is_string($value) ? trim($value) : $value;
            if (is_numeric($key) || $this->is_bool($value, ['', null])) {
                $k = ($value && $this->is_false($value, ['', null])) ? : $key;
                # var_dump([$value, $this->is_false($value), $k]);
                if (!$this->is_false($value, ['', null])) {
                    $url = array_key_exists($k, $this->namespace) ? $this->namespace[$k] : '';
                    $this->xmlns = array_merge([$k => $url], $this->xmlns);
                }
                
                
            } else {
                $this->xmlns[$key] = $value;
            }
        }
    }

    public function nsTagName($key)
    {
        $item = preg_replace('/\s+/', '', ucwords($key));
        if (preg_match('/^([a-z]+):/i', $item, $matches)) {
            $k = $matches[1];
            $ns = $this->ns[$k];            
            $item = $ns . ':' . explode(':', $item)[1];
        }
        return $item;
    }

    public function shortName($value)
    {
        return $this->shortName = $value;
    }

    public function description($value)
    {
        return $this->description = $value;
    }

    public function tags($value)
    {
        return $this->tags = $value;
    }

    public function contact($value)
    {
        return $this->contact = $value;
    }

    public function url($variable)
    {
        if (is_string($variable)) {
            return $this->url[] = ['template' => $this->urlTemplate($variable), 'type' => 'text/html'];
        }

        if (!is_array($variable)) {
            return $this->url;
        }

        foreach ($variable as $key => $value) {
            if (is_string($value)) {
                $this->url[] = ['template' => $this->urlTemplate($value), 'type' => 'text/html'];
            } elseif (is_array($value)) {
                $rel = isset($value['rel']) ? $value['rel'] : '';
                if (isset($value[0])) {
                   $value['template'] =  $value[0];
                   unset($value[0]);
                }
                
                $value['template'] = $this->urlTemplate($value['template']);
                
                if (!isset($value['type'])) {
                    $value['type'] = 'text/html';
                    if ('self' == $rel) {
                        $value['type'] = 'application/opensearchdescription+xml';
                    }
                }
                $attr = 'template,type,rel';
                $this->url[] = $this->ksort($value, $attr);
            }
        }
        return $this->url;
    }

    public function urlTemplate($url)
    {
        $URL = parse_url($url);
        # print_r($URL);
        if (isset($URL['query'])) {
            $query = preg_replace(['/{([^}]+)}/i', '/:/', '/\?/'], ['__7B__${1}__7D__', '__3A__', '__3F__'], $URL['query']);
            $this->parse_str($query, $query_data);
            # print_r($query_data);
            foreach ($query_data as $key => $value) {
                if (preg_match('/__7B__([a-z]+)__3A__/i', $value, $matches)) {
                    $k = $matches[1];
                    if (array_key_exists($k, $this->namespace)) {
                        $this->xmlns = array_merge([$k => $this->namespace[$k]], $this->xmlns);
                    }
                }
            }
            $query_str = http_build_query($query_data, '', '&amp;');
            $URL['query'] = preg_replace(['/__3A__/', '/__3F__/', '/__7B__/', '/__7D__/'], [':', '?', '{', '}'], $query_str);
        }
        return $url = $this->http_build_url($URL);
    }

    public static function template($url)
    {
        $instance = new static;
        return $instance->urlTemplate($url);
    }

    public function image($variable)
    {
        $arr = [
            'ico' => 'image/x-icon',
        ];

        if (is_string($variable)) {
            $variable = [$variable];
        }

        $URL = parse_url($variable[0]);
        $pathinfo = pathinfo($URL['path']);
        $extension = _isset($pathinfo, 'extension', '');
        $ext = strtolower($extension);

        if (!isset($variable['type']) || $variable['type']) {
            if (array_key_exists($ext, $arr)) {
                $variable['type'] = $arr[$ext];
            }
        }
        # print_r([$variable, $URL, $pathinfo]);# 

        if (!isset($variable['height']) || !$variable['height']) {
            if (preg_match('/^(png)$/', $ext)) {
                $variable['height'] = 64;
            } else {
                $variable['height'] = 16;
            }
        }

        if (!isset($variable['width']) || !$variable['width']) {
            $variable['width'] = $variable['height'];
        }

        return $this->image = $variable;
    }

    public function parse_url($url, $component = -1, $completion = null)
    {
        $URL = parse_url($url);
        if ($completion) {
            $URL['scheme'] .= '://';
            if ($URL['query']) {
                $URL['query'] = '?' . $URL['query'];
            }
        }

        if (-1 != $component) {

        }
        return $URL;
    }

    public function parse_str($encoded_string, &$result)
    {
        parse_str($encoded_string, $query_data);
        foreach ($query_data as $key => $value) {
            $k = preg_replace('/^amp;/i', '', $key);
            $result[$k] = $value;
        }
    }

    public function is_false($var, $custom = false, $number = null)
    {
        $var = $this->strtoint($var);
        $arr = [false, 0];
        if ($number) {
            array_pop($arr);
        }
        $arr =$this->arr_merge($arr, $custom, false);
        # var_dump($arr);
        return $this->in_arr($var, $arr);
    }

    public function is_true($var, $custom = true, $number = null)
    {
        $var = $this->strtoint($var);
        $arr = [true, 1];
        if ($number) {
            array_pop($arr);
        }
        $arr =$this->arr_merge($arr, $custom, true);
        return $this->in_arr($var, $arr);
    }

    public function is_bool($var, $custom = false, $number = null)
    {
        $var = $this->strtoint($var);
        $arr = [true, false, 1, 0];
        if ($number) {
            $this->arr_pop($arr, 2);
        }
        $arr =$this->arr_merge($arr, $custom, false);
        return $this->in_arr($var, $arr);
    }

    public function strtoint($str)
    {
        return $str = is_numeric($str) ? (int) $str : $str;
    }

    public function in_arr($var, $arr)
    {
        foreach ($arr as $row) {
            if ($row === $var) {
                return true;
            }
        }
        return false;
    }

    public function arr_merge($arr, $custom, $exclude = '__EXCLUDE__')
    {
        if (is_array($custom)) {
            $arr = array_merge($arr, $custom);
        } elseif ('__EXCLUDE__' !== $custom) {
            array_push($arr, $custom);
        }
        return $arr;
    }

    public function arr_pop(&$array, $limit = 1)
    {
        for ($i = 0; $i < $limit; $i++) {
            array_pop($array);
        }
    }

    public function http_build_url($data, $completion = true)
    {
        # print_r($data);
        
        $variable = ['scheme' => '://', 'host', 'path', 'query' => ['?']];
        if ($completion) {
            foreach ($data as $key => $value) {
                $item = isset($variable[$key]) ? $variable[$key] : '';
                if (is_array($item)) {
                    foreach ($item as $row) {
                        $data[$key] = $row . $data[$key];
                    }

                } elseif ($item) {
                    $data[$key] = $value . $item;
                }
            }
        }
        # print_r($data);
        return $url = implode('', $data);
        
    }

    public function ksort(&$array, $sort_flags = SORT_REGULAR)
    {
        if (is_int($sort_flags)) {
            ksort($array);
            return $array;
        }
        
        if (is_string($sort_flags)) {
            $sort_flags = explode(',', $sort_flags);
        }

        if (!is_array($sort_flags)) {
            return $array;
        }

        $arr = [];
        foreach ($sort_flags as $a) {
            if (isset($array[$a])) {
                $arr[$a] = $array[$a];
                unset($array[$a]);
            }
        }
        return $array = array_merge($arr, $array);
    }
}
