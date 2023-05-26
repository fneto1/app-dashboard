<?php

//classe dashboard

use PSpell\Config;

class Dashboard {
    public $data_inicio;
    public $data_fim;
    public $numeroVendas;
    public $totalVendas;
    public $clienteAtivo;
    public $clienteInativo;
    public $critica;
    public $sugestao;
    public $elogio;
    public $despesas;

    public function __get($atributo){
        return $this->$atributo;
    }

    public function __set($atributo, $valor){
        $this->$atributo = $valor;
    }
}

//classe de conexão
class Conexao {
    private $host = 'localhost';
    private $dbname = 'dashboard';
    private $user = 'root';
    private $password = '';

    public function conectar(){
        try{

            $conexao = new PDO(
                "mysql:host=$this->host;dbname=$this->dbname", $this->user, 
                $this->password
            );

            $conexao->exec('set charset utf8');

            return $conexao;

        } catch(PDOException $e){
            echo '<p>'.$e->getMessage().'</p>';
        }
    }
}

//classe(modelo)
class Bd{
    private $conexao;
    private $dashboard;

    public function __construct(Conexao $conexao, Dashboard $dashboard){
        $this->conexao = $conexao->conectar();
        $this->dashboard = $dashboard;
    }

    public function getNumeroVendas(){
        $query = "SELECT COUNT(*) 
        AS numero_vendas 
        FROM tb_vendas 
        WHERE data_venda 
        BETWEEN :data_inicio AND :data_fim;";

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->numero_vendas;
    }

    public function getTotalVendas(){
        $query = "SELECT SUM(total) 
        AS total_vendas 
        FROM tb_vendas 
        WHERE data_venda 
        BETWEEN :data_inicio AND :data_fim;";

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->total_vendas;
    }

    public function getClientesAtivos(){
        $query = "SELECT COUNT(*) AS clientes_ativos FROM tb_clientes WHERE cliente_ativo = 1;";

        $stmt = $this->conexao->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->clientes_ativos;
    }

    public function getClientesInativos(){
        $query = "SELECT COUNT(*) AS clientes_inativos FROM tb_clientes WHERE cliente_ativo = 0;";

        $stmt = $this->conexao->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->clientes_inativos;
    }

    public function getCriticas(){
        $query = "SELECT COUNT(*) AS criticas FROM tb_contatos WHERE tipo_contato = 1;";

        $stmt = $this->conexao->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->criticas;
    }

    public function getSugestoes(){
        $query = "SELECT COUNT(*) AS sugestoes FROM tb_contatos WHERE tipo_contato = 2;";

        $stmt = $this->conexao->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->sugestoes;
    }

    public function getElogios(){
        $query = "SELECT COUNT(*) AS elogios FROM tb_contatos WHERE tipo_contato = 3;";

        $stmt = $this->conexao->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->elogios;
    }

    public function getDespesas(){
        $query = "SELECT SUM(total) 
        AS total_despesas 
        FROM tb_despesas 
        WHERE data_despesa 
        BETWEEN :data_inicio AND :data_fim;";

        $stmt = $this->conexao->prepare($query);
        $stmt->bindValue(':data_inicio', $this->dashboard->__get('data_inicio'));
        $stmt->bindValue(':data_fim', $this->dashboard->__get('data_fim'));
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ)->total_despesas ;
    }
}

$dashboard = new Dashboard();
$conexao = new Conexao();

$competencia = explode('-', $_GET['competencia']);
$ano = $competencia[0];
$mes = $competencia[1];

$dias_do_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

$dashboard->__set('data_inicio', $ano.'-'.$mes.'-01');
$dashboard->__set('data_fim', $ano.'-'.$mes.'-'.$dias_do_mes);

$bd = new Bd($conexao, $dashboard);

$dashboard->__set('numeroVendas', $bd->getNumeroVendas());
$dashboard->__set('totalVendas', $bd->getTotalVendas());
$dashboard->__set('clienteAtivo', $bd->getClientesAtivos());
$dashboard->__set('clienteInativo', $bd->getClientesInativos());
$dashboard->__set('critica', $bd->getCriticas());
$dashboard->__set('sugestao', $bd->getSugestoes());
$dashboard->__set('elogio', $bd->getElogios());
$dashboard->__set('despesas', $bd->getDespesas());
echo json_encode($dashboard);
?>