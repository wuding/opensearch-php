<?php

$config = include 'config/opensearch.php';
$generate = new \OpenSearch\Generate($config);
echo $generate->xml();
