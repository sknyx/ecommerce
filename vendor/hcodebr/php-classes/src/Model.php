<?php

namespace Hcode;

class Model {

    private $values = []; //dados do usuário

    public function __call( $name, $args ){ //método para saber toda vez que o método for chamado - $name = nome do método que foi chamado,                                               $args = argumentos passados para o método

        $method = substr($name, 0, 3);   //conferindo que tipo de método é - get ou set -> pegando as 3 primeira letras para definir se é get ou set
            $fieldName = substr( $name, 3, strlen($name) ); //conferindo o nome do campo passado para o método -> pegando o restante das letras para descobrir o nome do campo

        /*var_dump( $method, $fieldName);
        exit;*/ //conferindo se as funções acima estão funcionando

        switch( $method )
        {
            case "get":
                return $this->values[ $fieldName ];
            break;

            case "set":
                $this->values[ $fieldName ] = $args[0];           
            break;
        }

    } 

    public function setData($data = array() )
    {
        foreach ($data as $key => $value) {

            $this->{"set".$key}($value); //criando um set dinâmico que serve para diversos campos
        
        }
    }

    public function getValues(){
        return $this->values;
    }


}

?>