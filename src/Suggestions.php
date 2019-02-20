<?php
namespace OpenSearch;

class Suggestions
{
    public $raw = null;
    public $data = null;
    public $lists = null;
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
    public $json = null;
    public $configFile = null;

    public function __construct($data = null, $query_string = null, $query_url = null)
    {
        $this->init($data, $query_string, $query_url);
        # func('\OpenSearch\_isset', '\_isset', [], '', null);
    }

    public function init($data = null, $query_string = null, $query_url = null)
    {
        $this->raw = $data;
        $this->query_string = $query_string;
        $this->query_url = $query_url;
    }

    public function configFile($path = null)
    {
    	if (null === $path) {
    		return $this->configFile;
    	} else {
    		$this->configFile = $path;
    	}
    	
		
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
    		$hash = md5($url);
    		if (isset($this->urls[$hash])) {
    			return $this->query_url_template = $this->urls[$hash];
    		}

    		if ($encode) {
    			$this->urls[$hash] = $url = Description::template($url);
    		}
    		return $this->query_url_template = $url;
    	}
    	return false;
    }

    public function json()
    {

    	$data = $this->data();
    	$lists = $this->lists();
    	$arr = [
    		$this->query_string,
    		$this->lists['complection'],
    		$this->lists['description'],
    		$this->lists['query_url'],
    	];
    	return $this->json = json_encode($arr);
    	print_r($lists);
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
    	$query_url = $this->queryUrl(0);
    	$query_url = $this->queryUrlTemplate($query_url, true);

    	$data = $data ? : $this->data;
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
}
