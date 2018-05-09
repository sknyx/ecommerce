<?php

namespace Hcode; //especificando o namespace da nossa classe

use Rain\Tpl; //especificando que pretendo usar mais um namespace (Rain, no caso) e a classe Tpl dentro desse namespace

class Page { //criando a classe Page

    private $tpl;
    private $options = [];
    //definindo opções padrão das páginas
    private $defaults = [
        "header" => true,
        "footer" => true,
        "data" => []
    ];

    private function setData( $data = array() ){
        foreach ( $data as $key => $value ) {
            $this->tpl->assign( $key, $value ); //atribuição de variáveis que vão aparecer no template
        }
    }

    //método mágico construct
    public function __construct( $opts = array(), $tpl_dir = "/views/" ){ //variável de opções

        $this->options = array_merge( $this->defaults, $opts ); // mesclando os arrays de opções - options e defaults. O ultimo array passado como atributo sobrescreve o segundo, logo é necessário atenção para a ordem. Após a mesclagem, esse array é colocado no atributo options

        // -- configurando o template --

        $config = array(
            "tpl_dir" => $_SERVER[ "DOCUMENT_ROOT" ].$tpl_dir, // especificando que a partir do nosso diretorio root, no nosso projeto, procura a pasta "tal". Pra isso usamos a variável de ambiente $_SERVER["DOCUMENT_ROOT"] que vai trazer onde está a pasta/o diretório root do servidor. Após o '.', estamos especificando aonde está o template.
            "cache_dir" => $_SERVER[ "DOCUMENT_ROOT" ]."/views-cache/",
            "debug" => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        // criando/instanciando o objeto Tpl
        $this->tpl = new Tpl; //para termos acesso ao tpl nos outros métodos, é mais interessante ele ser um atributo da classe.

        //Os dados do merge estarão na chave 'data' do options
       $this->setData( $this->options[ "data" ] );

        if ( $this->options["header"] === true )
            $this->tpl->draw( "header" ); //desenhando o template na tela

    }

    public function setTpl( $name, $data = array(), $returnHTML = false ){ //método que carrega o conteúdo da página

        $this->setData( $data );
        return $this->tpl->draw( $name, $returnHTML );

    }

    public function __destruct(){ //método mágico destruct

        if ( $this->options["footer"] === true )
            $this->tpl->draw( "footer" );

    }
}

//separando os templates dessa maneira, facilitamos a forma de mexer no html. Caso seja necessária mudança no cabeçalho, vamos diretamente para o 'header.html', caso seja necessário mexer no conteúdo -> 'index.html', para o rodapé -> 'footer.html'

?>