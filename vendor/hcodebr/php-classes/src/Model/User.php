<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;

class User extends Model
{

    const SESSION = "User";
    const FORGOT_SECRET = "HcodePhp7_Secret"; //chave para descriptografar senha

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
            !$_SESSION[User::SESSION] //verificando se não é um session vazia
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 //verificando o id do usuário - ao colocar um (int) na frente, estamos fazendo um cast que deve retornar com algum valor int, caso contrário ele retorna vazio e ao fazer o cast é retornado o valor 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //verificando se é um usuário da administração, caso contrário não tem permissão para usar a página
        ) 
        {
            header( "Location: /admin/login" ); //redireciona para a tela de login
            exit;
        }

    }

    public static function logout() //o logout vai simplesmente excluir a session
    {
        $_SESSION[User::SESSION] = NULL; //"destruindo" a session
    }

    public static function listAll(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons AS b USING(idperson)");

    }

    public function get($iduser)
    {
 
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
        ":iduser"=>$iduser
        ));
        
        $data = $results[0];
        
        $this->setData($data);
 
    }

    public function save(){
        
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));

        $this->setData($results[0]);

    }

    public function update(){

        $sql = new Sql();
        
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            
            ":iduser" => $this->getiduser(),
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));

        $this->setData($results[0]);
        
    }

    public function delete(){

        $sql = new Sql();
        
        $results = $sql->query("CALL sp_users_delete(:iduser)", array(
            
            ":iduser" => $this->getiduser()

        ));
    }

	public function getForgot($email, $admin = true)
	{
        $sql = new Sql();
        
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE b.desemail = :desemail", array(
			":desemail"=>$email
        ));
        
		if (count($results) > 0)
		{
            $data = $results[0];
            
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data['iduser'],
				":desip"=>$_SERVER["REMOTE_ADDR"]
            ));
            
            $recoveryData = $results2[0];
            
			$encrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::FORGOT_SECRET, $recoveryData['idrecovery'], MCRYPT_MODE_ECB);
            
            $encryptCode = base64_encode($encrypt);
			
			if ($admin === true) {
				$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=";
			} else {
				$link = "http://www.hcodecommerce.com.br/forgot/reset?code=";
            }
            
			$mailer = new Mailer(
				$email, 
				$data['desperson'],
				"Redefinição de senha da Hcode Store", 
				"forgot", 
			array(
				"name"=>$data['desperson'],
				"link"=>$link.$encryptCode
            ));
            
			return $mailer->send();
		}
		else
		{
			throw new \Exception("Não foi possível redefinir a senha.");
		}
    }
    
	public static function validForgotDecrypt($code)
	{
		$code = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::FORGOT_SECRET, base64_decode($code), MCRYPT_MODE_ECB));
        
        $sql = new Sql();
        
        $results = $sql->select(" SELECT * FROM tb_userspasswordsrecoveries a 
			INNER JOIN tb_users b USING(iduser) 
			INNER JOIN tb_persons c USING(idperson) 
			WHERE 
				a.idrecovery = :idrecovery 
			    AND 
			    a.dtrecovery IS NULL 
			    AND 
			    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$code
        ));
        
		if (count($results) === 0)
		{
			throw new \Exception("Recuperação inválida.");
		}
		else
		{
			
			return $results[0];
		}
	}
}

?>