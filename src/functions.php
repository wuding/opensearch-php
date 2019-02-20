<?php

namespace OpenSearch;

/**
 * null合并运算符
 * 
 * http://php.net/manual/zh/migration70.new-features.php
 *
 * @param      array|object   $arr    数组对象
 * @param      string         $key    键名
 * @param      mixed          $value  默认值
 *
 * @return     mixed                  运算结果
 */
function _isset($arr, $key = '', $value = null)
{
    if (is_object($arr)) {
        $arr = (array) $arr;
    }

    // 大于等于 7.0
    if (version_compare(phpversion(), '7.0.0', '>=')) {
        eval("\$result = \$arr[\$key] ?? \$value;");
        return $result;
    }

    // 低版本
    return isset($arr[$key]) ? $arr[$key] : $value;
}

function func($origin, $name)
{
	if (!isset($GLOBALS['FUNC_ARGS'])) {
		$GLOBALS['FUNC_ARGS'] = [];
	}
	/*
	if (!isset($GLOBALS['FUNC_ARGS'][$name])) {
		$GLOBALS['FUNC_ARGS'][$name] = [];
	}
	*/
	$GLOBALS['FUNC_ARGS'][] = $name;
	$total = count($GLOBALS['FUNC_ARGS']);
	$ns = '';
	if (preg_match('/(.*)\\\([a-z_]+)$/i', $name, $matches)) {
		# print_r($matches);
		$ns = $matches[1];
		$name = $matches[2];
	}
	$namespace = $ns;
	$namespace = preg_replace('/^\\\|\\\$/', '', $namespace);
	


	$num = func_num_args();
	$arr = [];
	$params = [];
	$alphabet = '_ abcdefghijklmnopqrstuvwxyz';
	for ($i = 2; $i < $num; $i++) {
		$j = $i - 2;
		$str = '';
		$value = func_get_arg($i);
		if (is_bool($value)) {
			$value = $value ? 'true' : 'false';

		} elseif (is_null($value)) {
			$value = 'null';

		} elseif(is_string($value)) {
			$value = "'$value'";

		} elseif(is_numeric($value)) {
			
		} elseif(is_array($value)) {			
			# $GLOBALS['FUNC_ARGS'][$name][$j] = $value;
			$nm = "FUNC_ARGS_$total" . "_$j";
			define($nm, $value);
			# $value = "\$GLOBALS['FUNC_ARGS']['$name'][$j]";
			$value = "$nm";
		} else {
			var_dump($value);
			print_r([__FILE__, __LINE__]);
			exit;
		}
		$key = $alphabet[$i];
		$str = '$' . "$key = $value";
		# echo $str . PHP_EOL;
		$arr[] = $str;
		$params[] = "\$$key";
	}
	$arg = implode(', ', $arr); #echo 
	$param = implode(', ', $params);
	# print_r($GLOBALS['FUNC_ARGS']);# 
	$arr = [];
	foreach ($GLOBALS as $k => $v) {
		$arr[] = $k;
	}
	# print_r($arr); 
	$str = "namespace $namespace { function $name($arg) { return $origin($param); } }"; #
	$class = $name;
	if ($namespace) {
		$class = "\\$namespace\\$name";		
	}
	$origin = preg_replace('/^\\\|\\\$/', '', $origin);
	
	

	$info = [
		'namespace' => $namespace, 
		'name' => $name, 
		'class' => $class, 
		'origin' => $origin, 
		func_get_args(), 
		__FILE__, 
		'line' => __LINE__
	];

	if ($class == $origin) {
		$info['line'] = __LINE__;
		debug($str, $info, 1);
	}
	if (!function_exists($class)) {
		eval($str);
	} else {
		$info['info'] = "Cannot redeclare $name()";
		$info['line'] = __LINE__;
		# debug($str, $info);
	}
}

function debug($str, $arr, $exit = null)
{
	echo $str;
	print_r($arr);
	if ($exit) {
		exit;
	}
}
