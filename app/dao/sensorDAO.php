<?php 
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

require_once 'conexao.php';


class SensorDAO extends Conexao{
    
    public function __construct(){
        $this->conectar();
    }


    //Salvando no banco de dados
    public function salvar(Sensor $sensor){
        $query = "insert into sensor_temp (tipo_sensor, central, descricao, valor, dt_hr) values (:tipo, :central, :desc,:valor, :dt_hr);";
        $stmt = $this->conectar()->prepare($query);
        $stmt->bindValue(":tipo", $sensor->__get('tipo_sensor'));
        $stmt->bindValue(":central", $sensor->__get('central'));
        $stmt->bindValue(":desc", $sensor->__get('descricao'));
        $stmt->bindValue(":valor", $sensor->__get('valor'));
        $stmt->bindValue(":dt_hr", $sensor->__get('dt_hr'));
      
        if($stmt->execute() == False){
            echo"<pre>";
              print_r($stmt->errorInfo());
            echo"</pre>";
        }

        $sensor->__set('id', $this->conectar()->lastInsertId());

        return $sensor;
    }


    public function pesquisarID($id){
        $query = "select * from sensor where id = :id";
        $stmt = $this->conectar()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $sensor = new Sensor($result->tipo_sensor, $result->central, $result->descricao, $result->valor);
        $sensor->__set('id', $result->id);

        return $sensor;
    }

    public function listar($cod){
        $query = "select * from central where cod = :cod";
        $stmt = $this->conectar()->prepare($query);
        $stmt->bindValue(':cod', $cod);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $sensores = array();

        $tiposensorDAO = new sensorDAO();
        

        foreach($result as $id => $objeto){
            $tipo = $tiposensorDAO->tipos_sensor($objeto->tipo_sensor);
            $sensor = new Sensor($tipo, $objeto->central, $objeto->descricao, $objeto->valor, $objeto->dt_hr);
            $sensor->__set('id', $objeto->id);

            $sensores[] = $sensor;
        }
        return $sensores;
    }

    

    public function tipos_sensor($id){
        $query = 'select * from tipo_sensor where id = :id';
        $stmt = $this->conectar()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_OBJ);

        $tipo_sensor = new TipoSensor($result->tipo, $result->icon, $result->color);
        $tipo_sensor->__set('id', $result->id);

        //var_dump($tipo_sensor); 
        return $tipo_sensor;

    }

    public function tipos_sensores(){
        $query = 'select * from tipo_sensor';
        $stmt = $this->conectar()->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $tipos_sensores = array();

        foreach($result as $id => $objeto){
            $tipo_sensor = new TipoSensor($objeto->tipo, $objeto->icon, $objeto->color);
            $tipo_sensor->__set('id', $objeto->id);


            $tipos_sensores[] = $tipo_sensor;
        }
        return $tipos_sensores;

    }

    public function listar_por_tipo($id, $user){
        $query = 'select * from sensor_temp s INNER JOIN central c ON s.central = c.cod where tipo_sensor = :id and c.usuario = :user order by dt_hr asc';
        $stmt = $this->conectar()->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':user', $user);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $sensores = array();
        $tiposensorDAO = new SensorDAO();

        foreach($result as $id => $objeto){
            $tipo = $tiposensorDAO->tipos_sensor($objeto->tipo_sensor);
            $sensor = new Sensor($tipo, $objeto->central, $objeto->descricao, $objeto->valor, $objeto->dt_hr);
            $sensor->__set('id', $objeto->id);

            $sensores[] = $sensor;
        }

        return $sensores;
    }
}

?>