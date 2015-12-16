<?php
use Amlun\Tools\StringFilter\Replace;
require_once __DIR__ . "/../autoload.php";

$replace = new Replace ( '/\\n\\n/', '<p></p>' );
$str = $replace->run ( "hello world.\n\nI am a super star.\n\n" );
var_dump ( $str );