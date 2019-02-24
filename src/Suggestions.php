<?php
namespace OpenSearch;

class Suggestions
{
    public $configFile = null;
    public $raw = null;    
    public $query_string = null;
    public $query_url = null;
    public $query_url_template = null;

    public $urls = [];
    public $columns = [
    	'complection' => '',
    	'description',
    	'query_url' => 'url',
    	'results' => 'total',
    ];
    public $data = null;
    public $lists = null;    
    public $json = null;
    public $json_description = null;
    public $json_query_url = null;

    public function __construct($raw_data = null, $query_string = null, $query_url = null)
    {
        $this->init($raw_data, $query_string, $query_url);
    }

    public function init($raw_data = null, $query_string = null, $query_url = null)
    {
        $this->raw = $raw_data;
        $this->query_string = $query_string;
        $this->query_url = $query_url;
    }

    public function configFile($path = null)
    {
    	if (null !== $path) {
    		$this->configFile = $path;
    	}
        return $this->configFile;
    }    

    public function json()
    {
        $data = $this->data();
    	$lists = $this->lists();
    	$arr = [
    		$this->query_string,
    		$this->lists['complection'],
    	];
        if ($this->json_description) {
            $arr[] = $this->lists['description'];
            if ($this->json_query_url) {
                $arr[] = $this->lists['query_url'];
            }
        }
    	return $this->json = json_encode($arr);
    }

    public function data($raw = null)
    {
    	$raw = $raw ? : $this->raw;
    	$arr = [];
    	foreach ($raw as $row) {
    		$r = $this->columns($row);
    		$arr[] = $r;
    	}
    	return $this->data = $arr;
    }

    public function columns($row)
    {
    	$arr = [];
    	foreach ($this->columns as $key => $value) {
    		$field = is_numeric($key) ? $value : $key;
    		$val = $value ? : $field;
    		$arr[$field] = _isset($row, $val, '');
            
    	}        
    	return $arr;
    }

    public function lists($data = null)
    {
    	$data = $data ? : $this->data;
        $query_url = $this->queryUrl(0);
    	$query_url = $this->queryUrlTemplate($query_url, true);    	
    	$arr = [
    		'complection' => [],
    		'description' => [],
    		'query_url' => [],
    		'results' => [],
    	];
    	foreach ($data as $row) {
    		if (!$row['description'] && -1 < $row['results']) {
    			$row['description'] = $row['results'] . ' results';
    		}
    		if (!$row['query_url'] && $this->query_url) {
    			$row['query_url'] = preg_replace('/{searchTerms}/', urlencode($row['complection']), $query_url);
    		}
    		foreach ($row as $key => $value) {
    			$arr[$key][] = $value;
    		}
    	}
    	return $this->lists = $arr;
    }

    public function queryUrl($url = null)
    {
        if (null === $url) {
            return $this->query_url;
        } elseif ($url) {
            $this->query_url = $url;
        }
        

        if (!$this->query_url && $this->configFile) {
            $config = include $this->configFile;
            $this->query_url = get_config_var('query_url');
        }
        return $this->query_url;
    }

    public function queryUrlTemplate($url = null, $encode = null)
    {
        if (null === $url) {
            return $this->query_url_template;
        } elseif ($url) {           
            if ($encode) {
                $url = Description::template($url);
            }
            $hash = md5($url);
            if (!isset($this->urls[$hash])) {
                $this->urls[$hash] = $url;
            }
            return $this->query_url_template = $url;
        }
        return false;
    }
}
