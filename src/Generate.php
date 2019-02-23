<?php
namespace OpenSearch;

class Generate
{
    public $osd = null;

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
}
