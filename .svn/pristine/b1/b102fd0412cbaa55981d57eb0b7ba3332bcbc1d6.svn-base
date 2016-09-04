<?php

# start
defined('SDK_PATH') 	or define('SDK_PATH', __DIR__.'/');
defined('EXT') 	or define('EXT', '.class.php');

require SDK_PATH. "Composer/ClassLoader.php";
$bundlers = SDK_PATH.'Bundlers/';
$loader = new \Composer\ClassLoader();


# autoload psr4
# =============================
$loader->setPsr4("Search\\", array($bundlers . "Search/"));
$loader->setPsr4("Plugin\\", array($bundlers . "Plugin/"));



# autoload namespaces
# =============================
$loader->set('Requests', array($bundlers . "Requests/"));


# autoload classmap
# =============================
// $loader->addClassMap($classMap);


# end
$loader->register(true);
