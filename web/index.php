<?php


$_NAMES = array(
	'' => 'opensearchdescription.xml',
	'opensearchdescription' => 'opensearchdescription.xml',
	'suggestions',
);


$Composer = require __DIR__ . '/../../../../vendor/autoload.php';
$functions = [
    '_isset' => ['', [], '', null],
    #'\Func\array_diff_kv' => ['', [], [], [], false],
    'str_match' => ['', '//', '', null, false],
    #'\Func\Arr\arr_fixed_assoc' => ['', [], false],
    #'arr_reset_values',
];
func($functions, ['variable', 'arr', 'pcre', 'filesystem']);




arr_fixed_assoc($_NAMES, true);
arr_reset_values($_NAMES, ['prefix' => __DIR__ .  '/../example/', 'suffix' => '.php'], true);
$basename = path_info(0, PATHINFO_BASENAME);
#print_r([__LINE__, get_defined_functions()['user'], get_included_files(), $_NAMES, $basename, $_NAMES[$basename]]);
if (array_key_exists($basename, $_NAMES) && include $_NAMES[$basename]) {
	//
} else {
	include $_NAMES[''];
}
/**/
# print_r([__LINE__, get_defined_constants(), get_defined_vars()]);
