<?php

require("Base.php");

class class_Vehiculo extends class_Base
{
  
  
  public function calcular_estados($id_movimiento = null, $id_entsal = null) {
  	if (is_null($id_entsal)) {
	  	$sql = "SELECT id_entsal FROM movimiento WHERE id_movimiento=" . $id_movimiento . " LIMIT 1";
	  	$rs = $this->mysqli->query($sql);
	  	$row = $rs->fetch_object();
	  	
	  	$id_entsal = $row->id_entsal;
	}
  	
  	$sql = "SELECT id_vehiculo, estado FROM entsal WHERE id_entsal=" . $id_entsal;
  	$rs = $this->mysqli->query($sql);
  	$row = $rs->fetch_object();
  	$id_vehiculo = $row->id_vehiculo;
  	$estado = $row->estado;
  	
	if ($estado != "S" && $estado != "A") {
  		$sql = "SELECT id_movimiento FROM movimiento WHERE id_entsal=" . $id_entsal . " AND estado='E'";
  		$rs = $this->mysqli->query($sql);
		$estado = (($rs->num_rows > 0) ? "T" : "E");
  	}
  	
  	$sql = "SELECT id_movimiento FROM movimiento WHERE id_entsal=" . $id_entsal . " AND estado<>'A' AND ISNULL(documentacion_id)";
  	$rs = $this->mysqli->query($sql);
  	$asunto = (($rs->num_rows > 0) ? "TRUE" : "FALSE");
  	
  	$sql = "SELECT id_movimiento FROM movimiento WHERE id_entsal=" . $id_entsal . " AND estado='D'";
  	$rs = $this->mysqli->query($sql);
  	$diferido = (($rs->num_rows > 0) ? "TRUE" : "FALSE");
  	
  	$sql = "UPDATE entsal SET estado='" . $estado . "', asunto=" . $asunto . ", diferido=" . $diferido . " WHERE id_entsal=" . $id_entsal;
  	$this->mysqli->query($sql);
  	
  	

  	if ($estado == "A") $estado = "S";
  	$sql = "UPDATE vehiculo SET estado='" . $estado . "' WHERE id_vehiculo=" . $id_vehiculo;
  	$this->mysqli->query($sql);
  	
  }
  
  
  public function calcular_totales($id_movimiento = null, $id_entsal = null) {
  	
  	if (is_null($id_entsal)) {
	  	$sql = "SELECT id_entsal FROM movimiento WHERE id_movimiento=" . $id_movimiento . " LIMIT 1";
	  	$rs = $this->mysqli->query($sql);
	  	$row = $rs->fetch_object();
	  	
	  	$id_entsal = $row->id_entsal;
	}
  	
  	if (! is_null($id_movimiento)) {
	  	$sql = "SELECT id_movimiento, SUM(total) AS total FROM reparacion WHERE id_movimiento=" . $id_movimiento . " GROUP BY id_movimiento";
	  	$rs = $this->mysqli->query($sql);
	  	$row = $rs->fetch_object();
	  	
	  	$sql = "UPDATE movimiento SET total=" . $row->total . " WHERE id_movimiento=" . $id_movimiento;
	  	$this->mysqli->query($sql);
  	}

  	
  	$sql = "SELECT id_entsal, SUM(total) AS total FROM movimiento WHERE id_entsal=" . $id_entsal . " GROUP BY id_entsal";
  	$rs = $this->mysqli->query($sql);
  	$row = $rs->fetch_object();
  	
  	$sql = "UPDATE entsal SET total=" . $row->total . " WHERE id_entsal=" . $id_entsal;
  	$this->mysqli->query($sql);
  	
  	

  	
  	
  	$sql = "SELECT id_vehiculo FROM entsal WHERE id_entsal=" . $id_entsal . " LIMIT 1";
  	$rs = $this->mysqli->query($sql);
  	$row = $rs->fetch_object();
  	
  	$id_vehiculo = $row->id_vehiculo;
  	
  	$sql = "SELECT id_vehiculo, SUM(total) AS total FROM entsal WHERE id_vehiculo=" . $id_vehiculo . " GROUP BY id_vehiculo";
  	$rs = $this->mysqli->query($sql);
  	$row = $rs->fetch_object();
  	
  	$sql = "UPDATE vehiculo SET total=" . $row->total . " WHERE id_vehiculo=" . $id_vehiculo;
  	$this->mysqli->query($sql);
  }
  
  
  public function method_leer_reparacion($params, $error) {
  	$p = $params[0];

  	$opciones = new stdClass;
  	$opciones->costo = "float";
  	$opciones->cantidad = "int";
  	$opciones->total = "float";
  	
	$sql = "SELECT reparacion.*, tipo_reparacion.descrip AS reparacion FROM reparacion INNER JOIN tipo_reparacion USING(id_tipo_reparacion) WHERE id_movimiento=" . $p->id_movimiento;
	
	return $this->toJson($sql, $opciones);
  }
  
  
  public function method_salida_taller($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT estado FROM movimiento WHERE id_movimiento=" . $p->id_movimiento;
  	$rs = $this->mysqli->query($sql);
  	$rowMovimiento = $rs->fetch_object();
  	
  	if ($rowMovimiento->estado == $p->movimiento_estado) {
  		$this->mysqli->query("START TRANSACTION");
  		
	  	$sql = "UPDATE movimiento SET f_sal=NOW(), id_usuario_sal='" . $_SESSION['login']->usuario . "', kilo=" . $p->kilo . ", estado='S' WHERE id_movimiento=" . $p->id_movimiento;
	  	$this->mysqli->query($sql);
	  	
	  	$this->auditoria($sql, $p->id_movimiento, "update_movimiento");
	  	
  		$sql = "DELETE FROM reparacion WHERE id_movimiento=" . $p->id_movimiento;
  		$this->mysqli->query($sql);
	  	
	  	foreach ($p->model as $item) {
	  		$set = $this->prepararCampos($item, "reparacion");
	  		
	  		$sql = "INSERT reparacion SET " . $set;
	  		$this->mysqli->query($sql);
	  		$insert_id = $this->mysqli->insert_id;
	  		
			$this->auditoria($sql, $insert_id, "insert_reparacion");
	  	}
	  	
	  	$this->calcular_totales($p->id_movimiento);
	  	$this->calcular_estados($p->id_movimiento);
	  	
	  	$this->mysqli->query("COMMIT");
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }
  
  
  public function method_diferir_salida_taller($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT estado FROM movimiento WHERE id_movimiento=" . $p->id_movimiento;
  	$rs = $this->mysqli->query($sql);
  	$rowMovimiento = $rs->fetch_object();
  	
  	if ($rowMovimiento->estado == $p->movimiento_estado) {
	  	$this->mysqli->query("START TRANSACTION");
	  	
	  	$sql = "UPDATE movimiento SET f_sal=NOW(), id_usuario_sal='" . $_SESSION['login']->usuario . "', estado='D' WHERE id_movimiento=" . $p->id_movimiento;
	  	$this->mysqli->query($sql);
	  	
	  	$this->auditoria($sql, $p->id_movimiento, "update_movimiento");
	  	
	  	$this->calcular_estados($p->id_movimiento);
	  	
	  	$this->mysqli->query("COMMIT");
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }

  
  public function method_entrada_taller($params, $error) {
	$p = $params[0];
	
  	$sql = "SELECT estado FROM entsal WHERE id_entsal=" . $p->id_entsal;
  	$rs = $this->mysqli->query($sql);
  	$rowEntsal = $rs->fetch_object();
  	
  	if ($rowEntsal->estado == $p->entsal_estado) {
		$this->mysqli->query("START TRANSACTION");
	
		$sql = "INSERT movimiento SET id_entsal=" . $p->id_entsal . ", id_taller=" . $p->id_taller . ", observa='" . $p->observa . "', f_ent=NOW(), id_usuario_ent='" . $_SESSION['login']->usuario . "', estado='E'";
		$this->mysqli->query($sql);
		$insert_id = $this->mysqli->insert_id;
		
		$this->auditoria($sql, $insert_id, "insert_movimiento");
	
		$this->calcular_estados($insert_id);
	
		$this->mysqli->query("COMMIT");
	
		return $insert_id;
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }
  
  
  public function method_anular_entrada_taller($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT estado FROM movimiento WHERE id_movimiento=" . $p->id_movimiento;
  	$rs = $this->mysqli->query($sql);
  	$rowMovimiento = $rs->fetch_object();
  	
  	if ($rowMovimiento->estado == $p->movimiento_estado) {
	  	$this->mysqli->query("START TRANSACTION");
	  	
	  	$sql = "UPDATE movimiento SET f_sal=NOW(), id_usuario_sal='" . $_SESSION['login']->usuario . "', total=0, estado='A' WHERE id_movimiento=" . $p->id_movimiento;
	  	$this->mysqli->query($sql);
	  	
	  	$this->auditoria($sql, $p->id_movimiento, "update_movimiento");
	  	
	  	$this->calcular_totales($p->id_movimiento);
	  	$this->calcular_estados($p->id_movimiento);
	  	
	  	$this->mysqli->query("COMMIT");
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }
  
  
  public function method_leer_movimiento($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();
  	

	$sql = "SELECT * FROM (";
	$sql.= "(SELECT movimiento.*, taller.descrip AS taller FROM movimiento LEFT JOIN taller USING(id_taller))";
	$sql.= " UNION ALL";
	$sql.= "(SELECT movimiento.*, temporal_1.descrip AS taller FROM movimiento INNER JOIN ";
		$sql.= "(";
		$sql.= "SELECT";
		$sql.= "  0 AS id_taller";
		$sql.= ", 'Parque Automotor' AS descrip";
		$sql.= ") AS temporal_1";
	$sql.= " USING(id_taller))";
	$sql.= ") AS temporal_2";
	$sql.= " WHERE id_entsal=" . $p->id_entsal;
	$sql.= " ORDER BY f_ent DESC";
	
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$row->kilo = (float) $row->kilo;
		$row->total = (float) $row->total;
		
		if ($row->estado == "D") $row->total = "Diferido";
		if ($row->estado == "A") $row->total = "Anulado";
		
		$row->bandera_estado = ($row->estado == "A") ? -1 : 0;
		
		$resultado[] = $row;
	}
	
	return $resultado;
  }
  
  
  public function method_entrada_vehiculo($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT estado FROM vehiculo WHERE id_vehiculo=" . $p->id_vehiculo;
  	$rs = $this->mysqli->query($sql);
  	$rowVehiculo = $rs->fetch_object();
  	
  	if ($rowVehiculo->estado == $p->vehiculo_estado) {
	  	$this->mysqli->query("START TRANSACTION");
	  	
	  	$sql = "UPDATE vehiculo SET estado='E' WHERE id_vehiculo=" . $p->id_vehiculo;
	  	$this->mysqli->query($sql);
	  	
	  	$this->auditoria($sql, $p->id_vehiculo, "update_vehiculo");
	  	
	  	$sql = "INSERT entsal SET id_vehiculo=" . $p->id_vehiculo . ", observa='" . $p->observa . "', f_ent=NOW(), id_usuario_ent='" . $_SESSION['login']->usuario . "', resp_ent='" . $p->resp_ent . "', kilo=" . $p->kilo . ", cod_up=" . $p->cod_up . ", asunto=FALSE, diferido=FALSE, estado='E'";
	  	$this->mysqli->query($sql);
	  	$insert_id = $this->mysqli->insert_id;
	  	
		$this->auditoria($sql, $insert_id, "insert_entsal");
	  	
	  	$this->mysqli->query("COMMIT");
	  	
	  	return $insert_id;
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }
  
  
  public function method_anular_entrada_vehiculo($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT estado FROM entsal WHERE id_entsal=" . $p->id_entsal;
  	$rs = $this->mysqli->query($sql);
  	$rowEntsal = $rs->fetch_object();
  	
	if ($rowEntsal->estado == $p->entsal_estado) {
		$fecha = date("Y-m-d H:i:s");

		$this->mysqli->query("START TRANSACTION");

		$sql = "UPDATE entsal SET f_sal='" . $fecha . "', id_usuario_sal='" . $_SESSION['login']->usuario . "', total=0, estado='A' WHERE id_entsal=" . $p->id_entsal;
		$this->mysqli->query($sql);
		
		$this->auditoria($sql, $p->id_entsal, "update_entsal");

		$sql = "UPDATE movimiento SET f_sal='" . $fecha . "', id_usuario_sal='" . $_SESSION['login']->usuario . "', total=0, estado='A' WHERE id_entsal=" . $p->id_entsal . " AND estado<>'A'";
		$this->mysqli->query($sql);
		
		$this->auditoria($sql, null, "update_movimiento");

		$this->calcular_totales(null, $p->id_entsal);
		$this->calcular_estados(null, $p->id_entsal);

		$this->mysqli->query("COMMIT");
	} else {
		$error->SetError(0, "estado");
		return $error;
	}
  }
  
  
  public function method_salida_vehiculo($params, $error) {
  	$p = $params[0];
  	
  	$sql = "SELECT estado FROM entsal WHERE id_entsal=" . $p->id_entsal;
  	$rs = $this->mysqli->query($sql);
  	$rowEntsal = $rs->fetch_object();
  	
  	if ($rowEntsal->estado == $p->entsal_estado) {
	  	$this->mysqli->query("START TRANSACTION");
	  	
	  	$sql = "UPDATE vehiculo SET estado='S' WHERE id_vehiculo=" . $p->id_vehiculo;
	  	$this->mysqli->query($sql);
	  	
	  	$this->auditoria($sql, $p->id_vehiculo, "update_vehiculo");
	
	  	$sql = "UPDATE entsal SET cod_up='" . $p->cod_up . "', f_sal=NOW(), id_usuario_sal='" . $_SESSION['login']->usuario . "', resp_sal='" . $p->resp_sal . "', estado='S' WHERE id_entsal=" . $p->id_entsal;
	  	$this->mysqli->query($sql);
	  	
	  	$this->auditoria($sql, $p->id_entsal, "update_entsal");
	  	
	  	$this->mysqli->query("COMMIT");
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }
  
  
  public function method_leer_vehiculo($params, $error) {
	$p = $params[0];

	$resultado = array();

	$sql = "SELECT vehiculo.*, tipo_vehiculo.descrip AS tipo FROM vehiculo INNER JOIN tipo_vehiculo USING(id_tipo_vehiculo) WHERE id_vehiculo=" . $p->id_vehiculo;
  	
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$row->total = (float) $row->total;
		
		
		
		$sql = "SELECT";
		$sql.= " CONCAT(localidad, ' (', departamento, ')') AS label";
		$sql.= " FROM _localidades INNER JOIN _departamentos USING(departamento_id)";
		$sql.= " WHERE localidad_id='" . $row->localidad_id . "'";
		
		$rsAux = $this->mysqli2->query($sql);
		if ($rsAux->num_rows > 0) {
			$rowAux = $rsAux->fetch_object();
			$row->localidad = $rowAux->label;
		} else {
			$row->localidad = "";
		}
		
		
		$sql = "SELECT";
		$sql.= " descrip AS label";
		$sql.= " FROM dependencia";
		$sql.= " WHERE id_dependencia='" . $row->id_dependencia . "'";
		
		$rsDependencia = $this->mysqli->query($sql);
		if ($rsDependencia->num_rows > 0) {
			$rowDependencia = $rsDependencia->fetch_object();
			$row->dependencia = $rowDependencia->label;
		} else {
			$row->dependencia = "";
		}
		
		
		
		$sql = "SELECT";
		$sql.= " descrip AS label";
		$sql.= " FROM depositario";
		$sql.= " WHERE id_depositario='" . $row->id_depositario . "'";
		
		$rsAux = $this->mysqli->query($sql);
		if ($rsAux->num_rows > 0) {
			$rowAux = $rsAux->fetch_object();
			$row->depositario = $rowAux->label;
		} else {
			$row->depositario = "";
		}
		
		
		
		$sql = "SELECT id_responsable AS model, CONCAT(apenom, ' - ', dni) AS label FROM responsable WHERE id_responsable='" . $row->id_responsable . "'";
		
		$rsResponsable = $this->mysqli->query($sql);
		if ($rsResponsable->num_rows > 0) {
			$rowResponsable = $rsResponsable->fetch_object();
			$row->responsable = $rowResponsable->label;
		} else {
			$row->responsable = "";
		}
		
		
		
		$resultado[] = $row;
	}

	$resultado = $resultado[0];

	return $resultado;
  }
  
  
  public function method_leer_entsal($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();

	$sql = "SELECT entsal.* FROM entsal WHERE id_vehiculo=" . $p->id_vehiculo . " ORDER BY f_ent DESC";
	
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$row->kilo = (float) $row->kilo;
		$row->total = (float) $row->total;
		
		if ($row->estado == "A") $row->total = "Anulado";
		
		$row->bandera_estado = ($row->estado == "A") ? -1 : 0;
		
		$resultado[] = $row;
	}
	
	return $resultado;
  }
  
  
  public function method_alta_modifica_vehiculo($params, $error) {
  	$p = $params[0];
  	
  	$id_vehiculo = $p->model->id_vehiculo;
  	
  	$sql = "SELECT id_vehiculo FROM vehiculo WHERE nro_patente='" . $p->model->nro_patente . "' AND id_vehiculo <> " . $id_vehiculo;
  	$rs = $this->mysqli->query($sql);
  	if ($rs->num_rows > 0) {
  		$error->SetError(0, "duplicado");
  		return $error;
  	} else {
  		
		$set = $this->prepararCampos($p->model, "vehiculo");
	  		
		if ($id_vehiculo == "0") {
	  		$sql = "INSERT vehiculo SET " . $set . ", id_parque=" . $_SESSION['parque']->id_parque . ", total=0, estado='S'";
	  		$this->mysqli->query($sql);
	  		$id_vehiculo = $this->mysqli->insert_id;
	  	
			$this->auditoria($sql, $id_vehiculo, "insert_vehiculo");
		} else {
	  		$sql = "UPDATE vehiculo SET " . $set . " WHERE id_vehiculo=" . $id_vehiculo;
	  		$this->mysqli->query($sql);
	  		
	  		$this->auditoria($sql, $id_vehiculo, "update_vehiculo");
		}
		
		if (is_file("documentos/comodato_0.jpg")) {
			if (is_file("documentos/comodato_" . $id_vehiculo . ".jpg")) unlink("documentos/comodato_" . $id_vehiculo . ".jpg");
			rename("documentos/comodato_0.jpg", "documentos/comodato_" . $id_vehiculo . ".jpg");
		}
		
		if (is_file("documentos/vehiculo_0.jpg")) {
			if (is_file("documentos/vehiculo_" . $id_vehiculo . ".jpg")) unlink("documentos/vehiculo_" . $id_vehiculo . ".jpg");
			rename("documentos/vehiculo_0.jpg", "documentos/vehiculo_" . $id_vehiculo . ".jpg");
		}
  	}
  }
  
  
  public function method_leer_gral($params, $error) {
  	$p = $params[0];
  	
  	$resultado = new stdClass;
  	$resultado->gral = array();
  	
  	$ent = 0;
  	$tal = 0;
  	$asu = 0;
  	$dif = 0;
 	
	$sql = "SELECT id_entsal, id_dependencia, nro_patente, CONCAT(nro_patente, '  ', marca) AS vehiculo, f_ent, f_sal, asunto, entsal.estado, entsal.diferido FROM entsal INNER JOIN vehiculo USING(id_vehiculo) WHERE vehiculo.id_parque=" . $_SESSION['parque']->id_parque . " AND entsal.estado<>'A' AND (entsal.estado='E' OR entsal.estado='T' OR entsal.asunto OR entsal.diferido) ORDER BY f_ent DESC";
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$row->asunto = (bool) $row->asunto;
		$row->diferido = (bool) $row->diferido;
		
		$sql = "SELECT";
		$sql.= "  descrip AS label";
		$sql.= " FROM dependencia";
		$sql.= " WHERE id_dependencia='" . $row->id_dependencia . "'";
		
		$rsDependencia = $this->mysqli->query($sql);
		if ($rsDependencia->num_rows > 0) {
			$rowDependencia = $rsDependencia->fetch_object();
			$row->dependencia = $rowDependencia->label;
		} else {
			$row->dependencia = "";
		}
		
		if ($row->estado == 'E') $ent+= 1;
		if ($row->estado == 'T') $tal+= 1;
		if ($row->asunto) $asu+= 1;
		if ($row->diferido) $dif+= 1;
		

		if ($p->ver == "Todos") {
			$resultado->gral[] = $row;
		} else if ($p->ver == "Entrada" && $row->estado == 'E') {
			$resultado->gral[] = $row;
		} else if ($p->ver == "Taller" && $row->estado == 'T') {
			$resultado->gral[] = $row;
		} else if ($p->ver == "Asunto" && $row->asunto) {
			$resultado->gral[] = $row;
		} else if ($p->ver == "Diferido" && $row->diferido) {
			$resultado->gral[] = $row;
		} else if (is_numeric($p->ver)) {
			$sql = "SELECT id_movimiento FROM movimiento WHERE id_entsal=" . $row->id_entsal . " AND estado <> 'A' AND id_taller=" . $p->ver;
			$rsMovimiento = $this->mysqli->query($sql);
			if ($rsMovimiento->num_rows > 0) $resultado->gral[] = $row;
		}
	}
	
	$resultado->statusBarText = "Entrada: " . $ent . ", Taller: " . $tal . ", Asunto: " . $asu . ", Diferido: " . $dif;
	
	return $resultado;
  }
  
  
  public function method_leer_asunto($params, $error) {
  	$p = $params[0];

	$sql = "SELECT 001_documentaciones.*, 001_documentaciones_tipos.documentacion_tipo FROM 001_documentaciones INNER JOIN 001_documentaciones_tipos USING(documentacion_tipo_id) WHERE documentacion_id='" . $p->documentacion_id . "'";
	$rs = $this->mysqli2->query($sql);
	if ($rs->num_rows == 0) {
  		$error->SetError(0, "documentacion_id");
  		return $error;
	} else {
		$row = $rs->fetch_object();
		$documento = (($row->documentacion_tipo_id=="1") ? $row->expediente_numero . "-" . $row->expediente_codigo . "-" . $row->expediente_ano : $row->documentacion_numero . "/" . $row->documentacion_numero_ano);
		$documento = $row->documentacion_tipo . " Nro. " . $documento;
		$row->documento = $documento;
		
		return $row;
	}
  }
  
  
  public function method_agregar_foto_comodato($params, $error) {
  	$p = $params[0];
  	
	if (is_file("documentos/comodato_0.jpg")) unlink("documentos/comodato_0.jpg");
	rename("php-traditional-server-master/files/" . $p->uuid . "/" . $p->uploadName, "documentos/comodato_0.jpg");
	rmdir("php-traditional-server-master/files/" . $p->uuid);
  }
  
  
  public function method_agregar_foto_vehiculo($params, $error) {
  	$p = $params[0];
  	
  	if (is_file("documentos/vehiculo_0.jpg")) unlink("documentos/vehiculo_0.jpg");
	rename("php-traditional-server-master/files/" . $p->uuid . "/" . $p->uploadName, "documentos/vehiculo_0.jpg");
	rmdir("php-traditional-server-master/files/" . $p->uuid);
  }
  
  
  public function method_preparar_foto($params, $error) {
	$p = $params[0];

	if (is_file("documentos/vehiculo_0.jpg")) unlink("documentos/vehiculo_0.jpg");
	if (is_file("documentos/comodato_0.jpg")) unlink("documentos/comodato_0.jpg");
  }
  
  
  public function method_asignar_asunto($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT documentacion_id FROM movimiento WHERE id_movimiento=" . $p->id_movimiento;
  	$rs = $this->mysqli->query($sql);
  	$row = $rs->fetch_object();
  	
  	if (is_null($row->documentacion_id)) {
		$sql = "SELECT documentacion_id FROM 001_documentaciones WHERE documentacion_id='" . $p->documentacion_id . "'";
		$rs = $this->mysqli2->query($sql);
		if ($rs->num_rows == 0) {
	  		$error->SetError(0, "documentacion_id");
	  		return $error;
		} else {
			$this->mysqli->query("START TRANSACTION");
			
			$sql = "UPDATE movimiento SET documentacion_id='" . $p->documentacion_id . "' WHERE id_movimiento=" . $p->id_movimiento;
			$this->mysqli->query($sql);
			
			$this->auditoria($sql, $p->id_movimiento, "update_movimiento");
			
			$this->calcular_estados($p->id_movimiento);
			
			$this->mysqli->query("COMMIT");
		}
  	} else {
		$error->SetError(0, "estado");
		return $error;
  	}
  }
  
  
  public function method_leer_parque($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();
  	
	$sql = "SELECT * FROM parque WHERE organismo_area_id='" . $p->organismo_area_id . "'";
	
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
	
	$resultado = $resultado[0];
	
	$_SESSION['parque'] = $resultado;
	
	return $resultado;
  }
  
  
  public function method_autocompletarVehiculo($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT CONCAT(nro_patente, '  ', marca) AS label, id_vehiculo AS model FROM vehiculo WHERE id_parque=" . $_SESSION['parque']->id_parque . " AND nro_patente LIKE '%" . $p->texto . "%' ORDER BY label";
	return $this->toJson($sql);
  }
  
  
  public function method_autocompletarVehiculoCompleto($params, $error) {
  	$p = $params[0];
  	
  	$resultado = array();
  	
	$sql = "SELECT * FROM vehiculo WHERE id_parque=" . $_SESSION['parque']->id_parque . " AND nro_patente LIKE '%" . $p->texto . "%' ORDER BY nro_patente, marca";
	
	$rs = $this->mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		unset($row->total);
		unset($row->estado);
		
		$rowAux = new stdClass;
		
		$rowAux->model = $row->id_vehiculo;
		$rowAux->label = $row->nro_patente . "  " . $row->marca;
		
		$rowAux->vehiculo = $row;
		
		
		
		$sql = "SELECT";
		$sql.= "  CONCAT(localidad, ' (', departamento, ')') AS label";
		$sql.= "  , localidad_id AS model";
		$sql.= " FROM _localidades INNER JOIN _departamentos USING(departamento_id)";
		$sql.= " WHERE localidad_id='" . $row->localidad_id . "'";
		
		$rsAux = $this->mysqli2->query($sql);
		if ($rsAux->num_rows > 0) $rowAux->cboLocalidad = $rsAux->fetch_object();
		

		
		$sql = "SELECT";
		$sql.= "  descrip AS label";
		$sql.= "  , id_dependencia AS model";
		$sql.= " FROM dependencia";
		$sql.= " WHERE id_dependencia='" . $row->id_dependencia . "'";
		
		$rsAux = $this->mysqli->query($sql);
		if ($rsAux->num_rows > 0) $rowAux->cboDependencia = $rsAux->fetch_object();
		
		
		
		$sql = "SELECT";
		$sql.= "  descrip AS label";
		$sql.= "  , id_depositario AS model";
		$sql.= " FROM depositario";
		$sql.= " WHERE id_depositario='" . $row->id_depositario . "'";
		
		$rsAux = $this->mysqli->query($sql);
		if ($rsAux->num_rows > 0) $rowAux->cboDepositario = $rsAux->fetch_object();
		
		
		
		
		$sql = "SELECT id_responsable AS model, CONCAT(apenom, ' - ', dni) AS label FROM responsable WHERE id_responsable='" . $row->id_responsable . "'";
		
		$rsAux = $this->mysqli->query($sql);
		if ($rsAux->num_rows > 0) $rowAux->cboResponsable = $rsAux->fetch_object();
		
		

		$row = $rowAux;
		
		$resultado[] = $row;
	}
	
	return $resultado;
  }
  
  
  public function method_autocompletarTipo_vehiculo($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT descrip AS label, id_tipo_vehiculo AS model FROM tipo_vehiculo WHERE descrip LIKE '%" . $p->texto . "%' ORDER BY label";
	return $this->toJson($sql);
  }
  
  
  public function method_autocompletarDependencia($params, $error) {
  	$p = $params[0];
	
	$sql = "SELECT id_dependencia AS model, descrip AS label FROM dependencia WHERE descrip LIKE'%" . $p->texto . "%' ORDER BY label";
	
	return $this->toJson($this->mysqli->query($sql));
  }
  
  
  public function method_autocompletarDepositario($params, $error) {
  	$p = $params[0];
  	
	$sql = "SELECT id_depositario AS model, descrip AS label FROM depositario WHERE descrip LIKE'%" . $p->texto . "%' ORDER BY label";
	
	return $this->toJson($this->mysqli->query($sql));
  }
  
  
  
  public function method_autocompletarUnipresu($params, $error) {
  	$p = $params[0];
  	
  	if (is_int($p->texto)) {
  		$sql = "SELECT cod_up AS model, CONCAT(nombre, ' - ', REPLACE(codigo, '-', '')) AS label FROM unipresu WHERE cod_up=" . $p->texto;
  	} else if (is_numeric($p->texto)) {
  		$sql = "SELECT cod_up AS model, CONCAT(REPLACE(codigo, '-', ''), ' - ', nombre) AS label FROM unipresu WHERE version=0 AND estado='A' AND REPLACE(codigo, '-', '') LIKE '%" . $p->texto . "%'";
  	} else {
  		$sql = "SELECT cod_up AS model, CONCAT(nombre, ' - ', REPLACE(codigo, '-', '')) AS label FROM unipresu WHERE version=0 AND estado='A' AND nombre LIKE '%" . $p->texto . "%'";
  	}
	
	return $this->toJson($sql);
  }
}

?>