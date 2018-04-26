<?php
session_start();
require_once "vendor/autoload.php";

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function () {

/*    --- Testando a conexão com o banco

$sql = new Hcode\DB\Sql();
$results = $sql->select("SELECT * FROM tb_users");
echo json_encode($results);
 */
    $page = new Page();

    $page->setTpl("index");

});

$app->get('/admin', function () { //criando rota para a pagina admin

	User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");

});

$app->get('/admin/login', function () { //criando rota para a pagina de login

    $page = new PageAdmin( [ //essa pagina não possue o mesmo header e footer das outras páginas, logo devemos desabilitar esses dois dessa maneira:

		"header" => false,
		"footer" => false
	] );

	$page->setTpl("login");
	
});

$app->post('/admin/login', function() {

	User::login( $_POST["login"], $_POST["password"] );

	header("Location: /admin");//redirecionar para a home page da administração caso tudo esteja ok com o login e senha
	exit;
});

$app->get('/admin/logout', function(){

	User::logout();

	header( "Location: /admin/login" );
    exit;
});


$app->run();
