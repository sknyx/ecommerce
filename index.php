<?php

session_start();
require_once "vendor/autoload.php";

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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

$app->get('/admin/logout', function(){ //rota para fazer o logout

	User::logout();

	header( "Location: /admin/login" );
    exit;
});

$app->get("/admin/users", function(){ //rota para a tela onde vai listar todos os usuários
	
	//User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array("users"=>$users) );

});

$app->get("/admin/users/create", function(){ //rota para a tela onde vai criar usuários
	
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

$app->get("/admin/users/:iduser/delete", function($iduser){  //rota para deletar as informações

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;	

});

$app->get("/admin/users/:iduser", function($iduser){ //rota para a tela onde vai dar update nos usuários
	//passamos ":iduser" por questão de boas práticas para rotas. É entendido que acessando via get /users/:idusuario estamos solicitando os dados de um usuário em específico
	
	User::verifyLogin();
	
	$user = new User();
 
	$user->get((int)$iduser); //carregando o usuário

	$page = new PageAdmin();

	$page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));

});

$app->post("/admin/users/create", function(){  //rota para salvar de fato as informações passadas no get->create
	
	User::verifyLogin();
	
		// var_dump($_POST); --> testando se a rota definida no action do html users-create está funcionando 

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; //verificando se o campo de direitos de administrador foi marcado. Caso sim, recebe o valor 1, caso contrário 0

	$user->setData($_POST); //dando set nos dados recebidos via post no form users-create
		
		// var_dump($user); --> testando o set dos dados passados acima
	
	$user->save();

	header("Location: /admin/users");
	exit;

});

$app->post("/admin/users/:iduser", function($iduser){  //rota para salvar de fato as informações passadas no get->:iduser
	
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; 

	$user->get((int)$iduser);
	
	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
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

$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::ListAll();

	$page = new PageAdmin();

	$page->setTpl("categories", array(
		"categories"=>$categories
	));

});

$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");

});

$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl("categories-update", array(
		"category"=>$category->getValues()));
});

$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$category->setData($_POST);
	$category->save();

	header("Location: /admin/categories");
	exit;
});

$app->run();
