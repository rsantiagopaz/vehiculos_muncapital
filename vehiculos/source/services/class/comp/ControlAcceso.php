<?php

class class_ControlAcceso
{
	protected $mysqli;
	
	function __construct() {
		require('Conexion.php');
		
		//session_unset();
		//session_destroy();
		session_start();
		
		$_SESSION["vehiculos_LAST_ACTIVITY"] = (int) $_SERVER["REQUEST_TIME"];
		$_SESSION["cookie_lifetime"] = (int) ini_get("session.cookie_lifetime");
		$_SESSION["gc_maxlifetime"] = (int) ini_get("session.gc_maxlifetime");

		
		$aux = new mysqli_driver;
		$aux->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;
		
		$this->mysqli = new mysqli("$servidor", "$usuario", "$password", "$base");
		$this->mysqli->query("SET NAMES 'utf8'");
	}


  public function method_login($params, $error) {
  	$p = $params[0];

	$_SESSION['login'] = $p->model;
  }

  
  public function method_traer_areas($params, $error) {
  	$p = $params[0];
  	
  	require('Conexion.php');
  	
  	$resultado = array();
  	
		
	$sql = "SELECT SYSusuario, sistema_id FROM _sistemas_usuarios WHERE SYSusuario='" . $p->usuario . "' AND sistema_id='017'";
	$rs = $this->mysqli->query($sql);
	
	if ($rs->num_rows == 1) {
	
		$sql = "SELECT * FROM _usuarios";
		$sql.= " LEFT JOIN _organismos_areas_usuarios ON _organismos_areas_usuarios.SYSusuario = _usuarios.SYSusuario";
		$sql.= " LEFT JOIN _organismos_areas ON _organismos_areas.organismo_area_id = _organismos_areas_usuarios.organismo_area_id";
		$sql.= " INNER JOIN parque ON BINARY parque.organismo_area_id = BINARY _organismos_areas_usuarios.organismo_area_id";
		$sql.= " LEFT JOIN _organismos ON _organismos.organismo_id = _organismos_areas.organismo_id";
		$sql.= " WHERE _usuarios.SYSusuario = BINARY '" . $p->usuario . "' AND _usuarios.SYSpassword = '" . md5($p->password) . "' AND _usuarios.SYSusuario_estado=1";
		
		$rs = $this->mysqli->query($sql);
		if ($rs->num_rows > 0) {
			while ($row = $rs->fetch_object()) {
				$rowAux = new stdClass;
				
				$rowAux->model = $row->organismo_area_id;
				$rowAux->label = $row->organismo_area . " - " . $row->organismo;
				
				$resultado[] = $rowAux;
			}
		}
	}
	
	return $resultado;
  }
}

?>