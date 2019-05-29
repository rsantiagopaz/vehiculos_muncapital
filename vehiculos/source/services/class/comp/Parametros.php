<?php

require_once("Base.php");

class class_Parametros extends class_Base
{


  public function method_autocompletarTipoReparacion($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT descrip AS label, id_tipo_reparacion AS model FROM tipo_reparacion WHERE descrip LIKE '%" . $p->texto . "%' ORDER BY label";
	return $this->toJson($this->mysqli->query($sql));
  }
  
  
  public function method_autocompletarTaller($params, $error) {
  	global $inventario;
  	
  	$p = $params[0];
  	
	if (is_numeric($p->texto)) {
		$sql = "SELECT";
		$sql.= "  id_proveedor AS model";
		$sql.= ", CONCAT(cuit, ' (', descrip, ')') AS label";
		$sql.= ", cuit";
		$sql.= ", descrip";
		$sql.= " FROM " . $inventario . ".proveedor";
		$sql.= " WHERE cuit LIKE '" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	} else {
		$sql = "SELECT * FROM (";
			$sql.= "(";
				$sql.= "SELECT";
				$sql.= "  id_proveedor AS model";
				$sql.= ", CONCAT(descrip, ' (', cuit, ')') AS label";
				$sql.= ", cuit";
				$sql.= ", descrip";
				$sql.= " FROM " . $inventario . ".proveedor";
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
  	global $inventario;
  	
  	$p = $params[0];
  	
	if (is_numeric($p->texto)) {
		$sql = "SELECT";
		$sql.= "  id_proveedor AS model";
		$sql.= ", CONCAT(cuit, ' (', descrip, ')') AS label";
		$sql.= ", cuit";
		$sql.= ", descrip";
		$sql.= " FROM " . $inventario . ".proveedor";
		$sql.= " WHERE cuit LIKE '" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	} else {
		$sql = "SELECT";
		$sql.= "  id_proveedor AS model";
		$sql.= ", CONCAT(descrip, ' (', cuit, ')') AS label";
		$sql.= ", cuit";
		$sql.= ", descrip";
		$sql.= " FROM " . $inventario . ".proveedor";
		$sql.= " WHERE descrip LIKE '%" . $p->texto . "%'";
		$sql.= " ORDER BY label";
	}
	
	return $this->toJson($sql);
  }

  
  
  public function method_leer_taller($params, $error) {
  	global $inventario;
  	
  	$p = $params[0];
  	
	$sql = "SELECT * FROM " . $inventario . ".proveedor WHERE id_proveedor=" . $p->id_proveedor;
	$rs = $this->mysqli->query($sql);
	$row = $rs->fetch_object();
	
	return $row;
  }
  
  
  public function method_agregar_taller($params, $error) {
  	global $inventario;
  	
  	$p = $params[0];
  	
	$sql = "INSERT " . $inventario . ".proveedor SET descrip='" . $p->descrip . "', cuit='" . $p->cuit . "'";
	$this->mysqli->query($sql);
	$id_proveedor = $this->mysqli->insert_id;
	
	$this->auditoria($sql, $id_proveedor, "insert_proveedor");
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
		
		$rsUni_presu = $this->mysqli2->query($sql);
		if ($rsUni_presu->num_rows > 0) {
			$rowUni_presu = $rsUni_presu->fetch_object();
			$row->uni_presu = $rowUni_presu->label;
		} else {
			$row->uni_presu = "";
		}
		
		$resultado[] = $row;
	}
	
	return $resultado;
  }
  
  
  public function method_autocompletarDepartamento($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();

	$sql = "SELECT departamento AS label, departamento_id AS model FROM _departamentos WHERE departamento LIKE '%" . $p->texto . "%' ORDER BY label";
	
	$rs = $this->mysqli2->query($sql);
	while ($row = $rs->fetch_object()) {
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