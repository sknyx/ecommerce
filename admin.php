<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

$app->get('/admin/logout', function(){ //rota para fazer o logout

	User::logout();

	header( "Location: /admin/login" );
    exit;
});

$app->get("/admin/forgot", function(){
	
	$page = new PageAdmin( [ //essa pagina não possue o mesmo header e footer das outras páginas, logo devemos desabilitar esses dois dessa maneira:

		"header" => false,
		"footer" => false
	] );

	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){

	User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;

});

$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin( [ //essa pagina não possue o mesmo header e footer das outras páginas, logo devemos desabilitar esses dois dessa maneira:

		"header" => false,
		"footer" => false
	] );

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin( [
		"header" => false,
		"footer" => false
	] );

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

	$user->setPassword($password);

	$page = new PageAdmin( [
		"header" => false,
		"footer" => false
	] );

	$page->setTpl("forgot-reset-success");

});
//
?>