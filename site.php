<?php

use \Hcode\Page;

$app->get('/', function () {

    /*    --- Testando a conexão com o banco
    
    $sql = new Hcode\DB\Sql();
    $results = $sql->select("SELECT * FROM tb_users");
    echo json_encode($results);
     */
        $page = new Page();
    
        $page->setTpl("index");
    
    });


?>