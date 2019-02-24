<?php
namespace OpenSearch;

class Generate
{
    public $osd = null;
    public static $funcArgs = [
        'link' => ['object,array', 'href' => null, 'title' => null],
    ];
    public static $argTypes = [
        'link' => ['object', 'array'],
    ];

    public function __construct($config = null)
    {
        $this->init($config);
    }

    public function init($config = null)
    {
        $this->osd = new Description($config);
    }

    public function xml($config = null)
    {
        $config = $config ?: $this->osd->config;
        unset($config['xmlns']);

        $attr = $this->xmlns($this->osd->xmlns);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"' . $attr . '>' . PHP_EOL;
        foreach ($config as $key => $value) {
            $item = preg_replace('/\s+/', '', ucwords($key));
            $item = $this->osd->nsTagName($key);
            $func = lcfirst($item);
            $func = preg_replace('/:/', '', $func);
            $val = $this->osd->$func;
            if (in_array($item, ['Url'])) {             
                $xml .= '  ' . $this->singleTag($item, $val) . PHP_EOL;

            } elseif (in_array($item, ['Image'])) {             
                $xml .= '  ' . $this->pairTag($item, $val) . PHP_EOL;

            } else {
                $xml .= "  <$item>$val</$item>" . PHP_EOL;
            }
            
        }
        $xml .= '</OpenSearchDescription>';
        return $xml;
    }

    public function xmlns($variable, $prefix = ' ', $suffix = '')
    {
        $pieces = [''];
        foreach ($variable as $key => $value) {
            $k = trim($key);
            $v = trim($value);
            if ($k && $v) {
                $pieces[] = "xmlns:$k=\"$v\"";
            }
        }

        $attr = implode(PHP_EOL . '    ', $pieces);
        if ($attr) {
            if ($prefix) {
                $attr = $prefix . $attr;
            }
            if ($suffix) {
                $attr .= $suffix;
            }
        }
        return $attr;
    }

    public function singleTag($item, $variable)
    {
        if ('Url' == $item) {
            return $this->url($variable);
        }
        return false;
    }


    public function url($variable)
    {
        $pieces = [];
        foreach ($variable as $key => $value) {
            $pieces[] = $this->urlTag($value);
        }
        return $tag = implode(PHP_EOL . '  ', $pieces);
    }

    public function urlTag($variable)
    {
        $pieces = [];
        foreach ($variable as $key => $value) {
            $pieces[] = "$key=\"$value\"";
        }
        $attr = implode(PHP_EOL . '       ', $pieces);
        return $tag = "<Url $attr />";
    }

    public function pairTag($item, $variable)
    {
        $val = $variable[0];
        unset($variable[0]);
        $pieces = [];
        foreach ($variable as $key => $value) {
            $pieces[] = "$key=\"$value\"";
        }
        $attr = implode(' ', $pieces);
        return $tag = "<$item $attr>$val</$item>";
    }

    public static function link()
    {
        $params = self::args(func_get_args(), __FUNCTION__);
        # print_r($params);exit;
        extract($params);
        $href = $href ? : $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/opensearch';
        # print_r($_SESSION);exit;
        $key = function () use (&$title, $href) {
            $title = trim($title);
            if (!$title) {
                $hash = md5($href);
                if (isset($_SESSION[$hash])) {
                    $title = $_SESSION[$hash];
                } else {
                    $contents = file_get_contents($href);
                    $xmlObj = new \SimpleXMLElement($contents);
                    $_SESSION[$hash] = $title = (string) $xmlObj->ShortName;
                }
            }
            $title = htmlspecialchars($title);
        };
        $key();
        return $key = '<link rel="search" type="application/opensearchdescription+xml" href="' . $href . "\" title=\"$title\" />";
        print_r(get_defined_vars());exit;
    }

    public static function args()
    {
        $arg = func_get_args();
        $args = $arg[0];
        $arr = self::$funcArgs[$arg[1]];
        # $types = self::$argTypes[$arg[1]];
        $types = array_shift($arr);
        $types = preg_split('/[,\s]+/', $types);
        $keys = array_keys($arr);
        $max = count($keys);
        $len = count($args);
        if ($len > $max) {
            $len = $max;
        }
        $params = $value = null;
        if ($args) {
            $value = $args[0];
            if (is_string($value) && !in_array('json', $types)) {
                $value = json_decode($value);
                # print_r([__LINE__, __FILE__, $value]);exit;
            } elseif (is_array($value) && in_array('array', $types)) {
                $value = null;

            } elseif (is_object($value) && in_array('object', $types)) {
                $value = null;
            }

            if (is_object($value)) {
                $value = (array) $value;
            }

            if (is_array($value)) {
                $params = [];
                foreach ($value as $key => $val) {
                    if (is_numeric($key)) {
                        $key = $keys[$key];
                    }
                    $params[$key] = $val;
                }
            }
        }
        if (null === $params) {
            $params = [];
            for ($i = 0; $i < $len; $i++) {
                $k = $keys[$i];
                $params[$k] = $args[$i];
            }
        }
        return $params = array_merge($arr, $params);
    }
}
