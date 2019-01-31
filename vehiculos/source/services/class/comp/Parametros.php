<?php

require("Base.php");

class class_Parametros extends class_Base
{
	
	
	
  public function method_alta_modifica_taller($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT id_taller FROM taller WHERE descrip='" . $p->model->descrip . "' AND id_taller <> " . $p->model->id_taller;
  	$rs = $this->mysqli->query($sql);
  	if ($rs->num_rows > 0) {
  		$error->SetError(0, "descrip");
  		return $error;
  	}
  	$sql = "SELECT id_taller FROM taller WHERE cuit='" . $p->model->cuit . "' AND id_taller <> " . $p->model->id_taller;
  	$rs = $this->mysqli->query($sql);
  	if ($rs->num_rows > 0) {
  		$error->SetError(0, "cuit");
  		return $error;
  	}
  	
	$id_taller = $p->model->id_taller;
	
	$set = $this->prepararCampos($p->model, "taller");
		
	if ($id_taller == "0") {
		$sql = "INSERT taller SET " . $set;
		$this->mysqli->query($sql);
		
		$id_taller = $this->mysqli->insert_id;
		
		$this->auditoria($sql, $id_taller, "insert_taller");
	} else {
		$sql = "UPDATE taller SET " . $set . " WHERE id_taller=" . $id_taller;
		$this->mysqli->query($sql);
		
		$this->auditoria($sql, $id_taller, "update_taller");
	}
	
	return $id_taller;
  }


  public function method_autocompletarTipoReparacion($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT descrip AS label, id_tipo_reparacion AS model FROM tipo_reparacion WHERE descrip LIKE '%" . $p->texto . "%' ORDER BY label";
	return $this->toJson($this->mysqli->query($sql));
  }
  
  
  public function method_autocompletarTaller($params, $error) {
  	$p = $params[0];
  	
	if (is_numeric($p->texto)) {
		$sql = "SELECT";
		$sql.= "  id_taller AS model";
		$sql.= ", CONCAT(cuit, ' (', descrip, ')') AS label";
		$sql.= ", cuit";
		$sql.= ", descrip";
		$sql.= " FROM taller";
		$sql.= " WHERE cuit LIKE '" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	} else {
		$sql = "SELECT * FROM (";
			$sql.= "(";
				$sql.= "SELECT";
				$sql.= "  id_taller AS model";
				$sql.= ", CONCAT(descrip, ' (', cuit, ')') AS label";
				$sql.= ", cuit";
				$sql.= ", descrip";
				$sql.= " FROM taller";
			$sql.= ") UNION (";
				$sql.= "SELECT";
				$sql.= "  0 AS model";
				$sql.= ", 'Parque Automotor' AS label";
				$sql.= ", '' AS cuit";
				$sql.= ", 'Parque Automotor' AS descrip";
			$sql.= ")";
		$sql.= ") AS temporal";
		$sql.= " WHERE descrip LIKE '%" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	}
	
	return $this->toJson($this->mysqli->query($sql));
  }
  
  
  public function method_autocompletarRazonSocial($params, $error) {
  	$p = $params[0];
  	
	if (is_numeric($p->texto)) {
		$sql = "SELECT";
		$sql.= "  id_taller AS model";
		$sql.= ", CONCAT(cuit, ' (', descrip, ')') AS label";
		$sql.= ", cuit";
		$sql.= ", descrip";
		$sql.= " FROM taller";
		$sql.= " WHERE cuit LIKE '" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	} else {
		$sql = "SELECT";
		$sql.= "  id_taller AS model";
		$sql.= ", CONCAT(descrip, ' (', cuit, ')') AS label";
		$sql.= ", cuit";
		$sql.= ", descrip";
		$sql.= " FROM taller";
		$sql.= " WHERE descrip LIKE '%" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	}
	
	return $this->toJson($sql);
  }

  
  
  public function method_leer_taller($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT * FROM taller WHERE id_taller=" . $p->id_taller;
	$rs = $this->mysqli->query($sql);
	$row = $rs->fetch_object();
	
	return $row;
  }
  
  
  public function method_agregar_taller($params, $error) {
  	$p = $params[0];
  	
	$sql = "INSERT taller SET descrip='" . $p->descrip . "', cuit='" . $p->cuit . "'";
	$this->mysqli->query($sql);
	$id_taller = $this->mysqli->insert_id;
	
	$this->auditoria($sql, $id_taller, "insert_taller");
  }
  
  
  public function method_agregar_parque($params, $error) {
  	$p = $params[0];
  	
	$sql = "INSERT parque SET descrip='" . $p->descrip . "', organismo_area_id='" . $p->organismo_area_id . "'";
	$this->mysqli->query($sql);
	$insert_id = $this->mysqli->insert_id;
	
	$this->auditoria($sql, $insert_id, "insert_parque");
	
	return $insert_id;
  }
  
  
  public function method_leer_parque($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();
  	
	$sql = "SELECT * FROM parque ORDER BY descrip";
	
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$sql = "SELECT";
		$sql.= "  CONCAT(_organismos_areas.organismo_area, ' (', _organismos.organismo, ')') AS label";
		$sql.= " FROM (_organismos_areas INNER JOIN _organismos USING(organismo_id))";
		$sql.= " WHERE _organismos_areas.organismo_area_id='" . $row->organismo_area_id . "'";
		
		$rsDependencia = $this->mysqli2->query($sql);
		if ($rsDependencia->num_rows > 0) {
			$rowDependencia = $rsDependencia->fetch_object();
			$row->dependencia = $rowDependencia->label;
		} else {
			$row->dependencia = "";
		}
		
		$resultado[] = $row;
	}
	
	return $resultado;
  }
  
  
  public function method_autocompletarLocalidad($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();

	$sql = "SELECT CONCAT(localidad, ' (', departamento, ')') AS label, localidad_id AS model FROM _localidades INNER JOIN _departamentos USING(departamento_id) WHERE localidad LIKE '%" . $p->texto . "%' ORDER BY label";
	
	$rs = $this->mysqli2->query($sql);
	while ($row = $rs->fetch_object()) {
		$resultado[] = $row;
	}
	
	return $resultado;
  }
}

?>