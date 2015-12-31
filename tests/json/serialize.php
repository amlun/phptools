<?php
/**
 * Created by PhpStorm.
 * User: lunweiwei
 * Date: 15/12/31
 * Time: 下午4:29
 */
require_once __DIR__ . "/../autoload.php";

class News
{
	public $id = 1;
	public $title = 'this is a time';
	public $author;
}

class Author
{
	public $name = 'lunweiwei';
	public $title = 'good man';
}

$json_serialize = new \Amlun\Tools\JsonSerializer();

$news = new News();
$author = new Author();
$news->author = $author;
$news_string = $json_serialize->serialize($news);
var_dump($news_string);

$json_string = '{"@type":"News","id":123,"title":"a big news","author":{"@type":"Author","name":"allan","title":"CEO"}}';
$news_object = $json_serialize->unserialize($json_string);
var_dump($news_object);