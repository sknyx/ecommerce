<?php


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

?>