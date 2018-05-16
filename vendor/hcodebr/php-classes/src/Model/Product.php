<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
//use \Hcode\Mailer;
use \Hcode\Model;

class Product extends Model
{
    public static function listAll(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

    }

    public function save(){
        
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight)", array(
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllength" => $this->getvllength(),
            ":vlweight" => $this->getvlweight()
        ));

        $this->setData($results[0]);

    }

    public function get($idproduct)
    {
 
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct;", array(
        ":idproduct"=>$idproduct
        ));
        
        $data = $results[0];
        
        $this->setData($data);
 
    }

    public function delete(){

        $sql = new Sql();
        
        $results = $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
            
            ":idproduct" => $this->getidproduct()

        ));
    }

    public function checkPhoto(){

        if(file_exists(
            $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.
            "resources".DIRECTORY_SEPARATOR.
            "site".DIRECTORY_SEPARATOR.
            "img".DIRECTORY_SEPARATOR.
            "products".DIRECTORY_SEPARATOR.
            $this->getidproduct().".jpg"
            )
        ) {
            $url = "/resources/site/img/products/".
            $this->getidproduct().".jpg";

        } else {

            $url = "/resources/site/img/default.jpg";

        }

        return $this->setdesphoto($url);
    }

    public function getValues(){
        
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file){

        $extension = explode('.', $file["name"]);
        $extension = end($extension);

        switch ($extension) {
            case "jpg":
            case "jpeg":
            $image = imgcreatefromjpeg($file["tmp_name"]);
            break;

            case "gif":
            $image = imgcreatefromgif($file["tmp_name"]);
            break;

            case "png":
            $image = imgcreatefrompng($file["tmp_name"]);
            break;
        }

        $dest = $_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR.
        "resources".DIRECTORY_SEPARATOR.
        "site".DIRECTORY_SEPARATOR.
        "img".DIRECTORY_SEPARATOR.
        "products".DIRECTORY_SEPARATOR.
        $this->getidproduct().".jpg";

        imagejpeg($image, $dest);

        imagedestroy($image);

        $this->checkPhoto();

    }

}

?>