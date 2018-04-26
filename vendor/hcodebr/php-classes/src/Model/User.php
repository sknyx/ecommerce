<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model{

    const SESSION = "User";

    public static function login( $login, $password ){

        $sql = new Sql();
        //consulta no banco de dados
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
        ":LOGIN" => $login
        ) );

        if (count($results)=== 0){
            throw new \Exception ("Usuário inexistente ou senha inválida."); //exceção gerada ao conferir login 
        }

        $data = $results[0];

        if ( password_verify( $password, $data[ "despassword" ] ) === true ){ //verificando se as senhas batem - senha fornecida na pagina com senha criptografada no banco de dados

            $user = new User();

            $user->setData( $data ); //mandando para o método setData, na classe Model, todos os dados vindos do banco de dados, para serem "setados"

            /*var_dump($user);
            exit;*/ //conferindo se as funções acima estão funcionado

            // para um login funcionar é necessário criar um sessão, os dados precisam estar em uma sessão, para que em cada página seja verificado se a sessão existe = se o usuário está devidamente logado, caso contrário o usuário será redirecionado para a página de login 

            $_SESSION[ User::SESSION ] = $user->getValues(); //colocando os dados do usuário logado dentro da sessão

            return $user;

        } else {
            throw new \Exception ("Usuário inexistente ou senha inválida.");            
        }
    }

    public static function verifyLogin( $inadmin = true ){ //o parâmetro verifica se o usuário é um administrador

        if(                                             //caso a sessão não esteja definida ou se a sessão está vazia, redireciona para a                                                         página de login
            !isset($_SESSION[ User::SESSION ]) 
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ) 
        {
            header( "Location: /admin/login" );
            exit;
        }

    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = NULL; //"destruindo" a session
    }

}



?>