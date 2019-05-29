<?php
session_start();

require_once("Base.php");

class class_Responsable extends class_Base
{
  

  public function method_alta_modifica_responsable($params, $error) {
  	$p = $params[0];
  	
  	/*
  	$sql = "SELECT dni FROM _personal WHERE dni='" . $p->model->dni . "'";
  	$rs = $this->mysqli->query($sql);
  	if ($rs->num_rows == 0) {
  		$error->SetError(0, "personal");
  		return $error;
  	}
  	*/
  	
  	$sql = "SELECT id_responsable FROM responsable WHERE dni='" . $p->model->dni . "' AND id_responsable <> " . $p->model->id_responsable;
  	$rs = $this->mysqli->query($sql);
  	if ($rs->num_rows > 0) {
  		$error->SetError(0, "dni");
  		return $error;
  	}

  	$sql = "SELECT id_responsable FROM responsable WHERE apenom='" . $p->model->apenom . "' AND id_responsable <> " . $p->model->id_responsable;
  	$rs = $this->mysqli->query($sql);
  	if ($rs->num_rows > 0) {
  		$error->SetError(0, "apenom");
  		return $error;
  	}
  	

	$set = $this->prepararCampos($p->model, "responsable");
		
	if ($p->model->id_responsable == "0") {
		$sql = "INSERT responsable SET " . $set;
		$this->mysqli->query($sql);
		
		$this->auditoria($sql, $this->mysqli->insert_id, "insert_responsable");
	} else {
		$sql = "UPDATE responsable SET " . $set . " WHERE id_responsable=" . $p->model->id_responsable;
		$this->mysqli->query($sql);
		
		$this->auditoria($sql, $p->model->id_responsable, "update_responsable");
	}
  }
  
  
  
  
  public function method_autocompletarResponsable($params, $error) {
  	$p = $params[0];
  	
  	if (is_numeric($p->texto)) {
  		$sql = "SELECT id_responsable AS model, CONCAT(dni, ' - ', apenom) AS label FROM responsable WHERE dni LIKE '%" . $p->texto . "%' ORDER BY label";
  	} else {
  		$sql = "SELECT id_responsable AS model, CONCAT(apenom, ' - ', dni) AS label FROM responsable WHERE apenom LIKE '%" . $p->texto . "%' ORDER BY label";
  	}
  	
	return $this->toJson($sql);
  }
  
  
  public function method_autocompletarResponsableCompleto($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();
  	
  	if (is_numeric($p->texto)) {
  		$sql = "SELECT id_responsable AS model, CONCAT(dni, ' - ', apenom) AS label, responsable.* FROM responsable WHERE dni LIKE '%" . $p->texto . "%' ORDER BY label";
  	} else {
  		$sql = "SELECT id_responsable AS model, CONCAT(apenom, ' - ', dni) AS label, responsable.* FROM responsable WHERE apenom LIKE '%" . $p->texto . "%' ORDER BY label";
  	}
  	
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$rowAux = new stdClass;
		
		$rowAux->model = $row->model;
		$rowAux->label = $row->label;
		
		unset($row->model);
		unset($row->label);
		
		$rowAux->responsable = $row;

		$row = $rowAux;
		
		$resultado[] = $row;
	}
  	
	return $resultado;
  }
}

?>