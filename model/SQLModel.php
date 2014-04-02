<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Cache\CacheManager as CacheManager;
use Illuminate\Database\Eloquent\Model as Eloquent;

$ini_array = parse_ini_file("./config.ini");

$capsule = new Capsule();
$capsule->addConnection(array(
	'driver'=> $ini_array['driver'],
	'host'=> $ini_array['host'],
	'database'=> $ini_array['dbname'],
	'username'=> $ini_array['user'],
	'password'=> $ini_array['password'],
	'charset'=> $ini_array['charset'],
	'collation'=> $ini_array['collation'],
	'prefix'=> $ini_array['prefix'],
	));

$capsule->setAsGlobal();
$capsule->bootEloquent();

?>
