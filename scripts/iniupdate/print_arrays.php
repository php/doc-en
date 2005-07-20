<?php

include 'ini_search_lib.php';

if ($argc == 1) die('specify a name!');

$array = $replace = array();

recurse($argv[1], true);

print_r($array);
print_r($replace);

?>
