<?php 

require_once("vendor/autoload.php");
use \Slim\Slim;
use \Hcode\Page;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
 
/*	--- Testando a conexão com o banco

	$sql = new Hcode\DB\Sql();
	$results = $sql->select("SELECT * FROM tb_users");
	echo json_encode($results);
*/
	$page = new Page();

	$page->setTpl( "index" );

});

$app->run();

 ?>