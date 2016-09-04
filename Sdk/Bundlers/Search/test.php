<?php

include "./autoload.php";
$est = new Search\ElasticSearch();
$est->setConf(['host'=>'127.0.0.1', 'port'=>9200]);
$est->setIndex('lr/og');
$res = $est->select();
var_dump($res);
