<?php
require_once __DIR__.'/aliyun_oss_libs/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

$loader->registerNamespaces(array(
    'Guzzle\\Common' => __DIR__.'/aliyun_oss_libs/guzzle/common',
    'Guzzle\\Parser' => __DIR__.'/aliyun_oss_libs/guzzle/parser',
    'Guzzle\\Plugin' => __DIR__.'/aliyun_oss_libs/guzzle/plugin',
    'Guzzle\\Stream' => __DIR__.'/aliyun_oss_libs/guzzle/stream',
    'Guzzle\\Http' => __DIR__.'/aliyun_oss_libs/guzzle/http',
    'Symfony\\Component\\EventDispatcher' => __DIR__.'/aliyun_oss_libs/symfony/event-dispatcher',
    'Symfony\\Component\\ClassLoader' => __DIR__.'/aliyun_oss_libs/symfony/class-loader',
    'Aliyun' => __DIR__.'/aliyun_oss_src',
));

$loader->register();
