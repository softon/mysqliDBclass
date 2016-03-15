<?php

include_once('config.inc.php');
include_once('Database.php');



$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
$result = $db->get("SELECT * FROM :table WHERE :name = ':value'",['table'=>'authors','name'=>'paperid','value'=>'P12206436'])->fetch();

$result = $db->count_rows('authors');

$result = $db->update('authors',['paperisd'=>'SHIBU']," id = '1'");

var_dump($result);



$db->close();