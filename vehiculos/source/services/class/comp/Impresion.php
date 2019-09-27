<?php
session_start();

require_once('Conexion.php');

set_time_limit(0);


$mysqli = new mysqli("$servidor", "$usuario", "$password", "$base");
$mysqli->query("SET NAMES 'utf8'");

$mysqli2 = new mysqli("$servidor2", "$usuario2", "$password2", "$base2");
$mysqli2->query("SET NAMES 'utf8'");

date_default_timezone_set("America/Argentina/Buenos_Aires");

switch ($_REQUEST['rutina'])
{
	
case "general" : {
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>General</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>ESTADO GENERAL</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big>Ver: <?php
		if (is_numeric($_REQUEST['ver'])) {
			$sql = "SELECT * FROM (";
				$sql.= "(";
					$sql.= "SELECT";
					$sql.= "   AS model";
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
			$sql.= " WHERE model=" . $_REQUEST['ver'];
			$sql.= " ORDER BY label";
			
			$rs = $mysqli->query($sql);
			$row = $rs->fetch_object();
			echo $row->label;
			
		} else {
			echo $_REQUEST['ver'];
		}
	?></big></td></tr>
	<tr><td>&nbsp;</td></tr>

	
	<?php
	
	$estado = array("E" => "Entrada", "S" => "Salida", "T" => "Taller");
	
	$p = new stdClass;
	$p->ver = $_REQUEST['ver'];
	
	$aux1 = array($p);
	$aux2 = array();
	
	require_once("Vehiculo.php");
	$vehiculo = new class_Vehiculo;
	$resultado = $vehiculo->method_leer_gral($aux1, $aux2);
	
	?>
	<tr><td colspan="20">
	<table border="1" rules="all" cellpadding="5" cellspacing="0" width="100%" align="center">
	<thead>
	<tr><th>Vehículo</th><th>Uni.presu.</th><th>Entrada</th><th>Salida</th><th>Estado</th><th>Asunto</th><th>Diferido</th></tr>
	</thead>
	<tbody>
	<?php
	
	foreach ($resultado->gral as $item) {
		?>
		<tr>
		<td><?php echo $item->vehiculo; ?></td>
		<td><?php echo $item->uni_presu; ?></td>
		<td><?php echo $item->f_ent; ?></td>
		<td><?php echo $item->f_sal; ?></td>
		<td><?php echo $estado[$item->estado]; ?></td>
		<td><?php echo ($item->asunto) ? "En trámite" : ""; ?>&nbsp;</td>
		<td><?php echo ($item->diferido) ? "En trámite" : ""; ?>&nbsp;</td>
		</tr>
		<?php
		
		if ($item->estado == "T") {

			$sql = "SELECT * FROM(";
			$sql.= "(SELECT movimiento.*, proveedor.descrip AS proveedor FROM movimiento INNER JOIN " . $inventario . ".proveedor USING(id_proveedor))";
			$sql.= " UNION ALL";
			$sql.= "(SELECT movimiento.*, temporal_1.descrip AS proveedor FROM movimiento INNER JOIN ";
				$sql.= "(";
				$sql.= "SELECT";
				$sql.= "  0 AS id_proveedor";
				$sql.= ", 'Parque Automotor' AS descrip";
				$sql.= ") AS temporal_1";
			$sql.= " USING(id_proveedor))";
			$sql.= ") AS temporal_2";
			$sql.= " WHERE id_entsal=" . $item->id_entsal . " AND estado='E'";
			$sql.= " ORDER BY f_ent DESC";
			
			
			$rs = $mysqli->query($sql);
			
			?>
			<tr>
			<td colspan="20">
			<table border="0" rules="rows" cellpadding="5" cellspacing="0" width="100%" align="center">
			<?php
			while ($row = $rs->fetch_object()) {
				?>
				<tr>
				<td>&nbsp;</td>
				<td><?php echo "# " . $row->id_movimiento; ?></td>
				<td><?php echo $row->proveedor; ?></td>
				<td><?php echo $row->f_ent; ?></td>
				<td><?php echo nl2br($row->observa); ?></td>
				<td>&nbsp;</td>
				</tr>
				<?php
			}
			?>
			</table>
			</td>
			</tr>
			<?php
		}
	}
	?>

	</tbody>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}



case "gastos" : {
	
	if (isset($_REQUEST['id_uni_presu'])) {
		$sql = "SELECT descrip FROM " . $inventario . ".uni_presu WHERE id_uni_presu=" . $_REQUEST['id_uni_presu'];
		$rsUni_presu = $mysqli->query($sql);
		$rowUni_presu = $rsUni_presu->fetch_object();
	}
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Gastos</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>LISTADO GASTOS</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big>Período: <?php echo $_REQUEST['desde'] . " / " . $_REQUEST['hasta']; ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<?php
	if (isset($_REQUEST['id_uni_presu'])) {
		?>
		<tr><td align="center" colspan="10"><big><b>Uni.presu.: <?php echo $rowUni_presu->descrip; ?></b></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
	}
	?>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="20">
	<table border="1" rules="all" cellpadding="5" cellspacing="0" width="100%" align="center">
	<thead>
	<tr><th>Vehículo</th><th>#</th><th>Taller</th>
	
	<?php
	if (! isset($_REQUEST['id_uni_presu'])) {
		?>
		<th>Uni.presu.</th>
		<?php
	}
	?>
	
	<th>Salida</th><th>Total</th></tr>
	</thead>
	<tbody>
	<?php
	
	$total = 0;
	


	
	$sql = "SELECT * FROM(";
	$sql.= "(SELECT movimiento.*, proveedor.descrip AS proveedor, vehiculo.nro_patente, vehiculo.nro_chasis, vehiculo.marca, uni_presu.id_uni_presu, uni_presu.descrip AS uni_presu_descrip FROM (((movimiento INNER JOIN " . $inventario . ".proveedor USING(id_proveedor)) INNER JOIN entsal USING(id_entsal)) INNER JOIN vehiculo USING(id_vehiculo)) INNER JOIN " . $inventario . ".uni_presu USING(id_uni_presu))";
	$sql.= " UNION ALL";
	$sql.= "(SELECT movimiento.*, temporal_1.descrip AS proveedor, vehiculo.nro_patente, vehiculo.nro_chasis, vehiculo.marca, uni_presu.id_uni_presu, uni_presu.descrip AS uni_presu_descrip FROM (((movimiento INNER JOIN ";
		$sql.= "(";
		$sql.= "SELECT";
		$sql.= "  0 AS id_proveedor";
		$sql.= ", 'Parque Automotor' AS descrip";
		$sql.= ") AS temporal_1";
	$sql.= " USING(id_proveedor)) INNER JOIN entsal USING(id_entsal)) INNER JOIN vehiculo USING(id_vehiculo)) INNER JOIN " . $inventario . ".uni_presu USING(id_uni_presu))";
	$sql.= ") AS temporal_2";
	$sql.= " WHERE estado='S'";
	
	if (isset($_REQUEST['id_uni_presu'])) $sql.= " AND id_uni_presu=" . $_REQUEST['id_uni_presu'];
	if (isset($_REQUEST['desde'])) $sql.= " AND DATE(f_sal) >= '" . $_REQUEST['desde'] . "'";
	if (isset($_REQUEST['hasta'])) $sql.= " AND DATE(f_sal) <= '" . $_REQUEST['hasta'] . "'";
	
	$sql.= " ORDER BY f_ent DESC";
	
	$rs = $mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$row->total = (float) $row->total;
		$total+= $row->total;
		
		$aux = array();
		if (! empty($row->nro_patente)) $aux[] = "n.p. " . $row->nro_patente;
		if (! empty($row->nro_chasis)) $aux[] = "n.ch. " . $row->nro_chasis;
		if (! empty($row->marca)) $aux[] = $row->marca;
		$row->vehiculo = implode(", ", $aux);
		
		
		?>
		<tr>
		<td><?php echo $row->vehiculo; ?></td>
		<td><?php echo $row->id_movimiento; ?></td>
		<td><?php echo $row->proveedor; ?></td>
		<?php
		if (! isset($_REQUEST['id_uni_presu'])) {
			?>
			<td><?php echo $row->uni_presu_descrip; ?></td>
			<?php
		}
		?>
		<td><?php echo $row->f_sal; ?></td>
		<td align="right"><?php echo number_format($row->total, 2, ",", "."); ?></td>
		</tr>
		<?php
	}
	?>
	
	<?php
	if (! isset($_REQUEST['id_uni_presu'])) {
		?>
		<tr><td colspan="6" align="right"><?php echo number_format($total, 2, ",", "."); ?></td></tr>
		<?php
	} else {
		?>
		<tr><td colspan="5" align="right"><?php echo number_format($total, 2, ",", "."); ?></td></tr>
		<?php
	}
	?>
	

	</tbody>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}



case "gastos" : {
	
	if (isset($_REQUEST['cod_up'])) {
		$sql = "SELECT CONCAT(REPLACE(codigo, '-', ''), ' - ', nombre) AS descrip FROM unipresu WHERE cod_up=" . $_REQUEST['cod_up'];
		$rsUnipresu = $mysqli->query($sql);
		$rowUnipresu = $rsUnipresu->fetch_object();
	}
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Gastos</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>LISTADO GASTOS</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big>Período: <?php echo $_REQUEST['desde'] . " / " . $_REQUEST['hasta']; ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<?php
	if (isset($_REQUEST['cod_up'])) {
		?>
		<tr><td align="center" colspan="10"><big>Unidad presup.: <?php echo $rowUnipresu->descrip; ?></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
	}
	?>

	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="20">
	<table border="1" rules="all" cellpadding="5" cellspacing="0" width="100%" align="center">
	<thead>
	<tr><th>Vehículo</th><th>#</th><th>Taller</th>
	
	<?php
	if (isset($_REQUEST['cod_up'])) {
		?>
		<th>Unidad presup.</th>
		<?php
	}
	?>
	
	<th>Salida</th><th>Total</th></tr>
	</thead>
	<tbody>
	<?php
	
	$total = 0;
	


	
	$sql = "SELECT * FROM(";
	$sql.= "(SELECT movimiento.*, proveedor.descrip AS proveedor, entsal.cod_up, vehiculo.nro_patente, vehiculo.marca, CONCAT(REPLACE(unipresu.codigo, '-', ''), ' - ', unipresu.nombre) AS up FROM (((movimiento INNER JOIN " . $inventario . ".proveedor USING(id_proveedor)) INNER JOIN entsal USING(id_entsal)) INNER JOIN vehiculo USING(id_vehiculo)) LEFT JOIN unipresu USING(cod_up))";
	$sql.= " UNION ALL";
	$sql.= "(SELECT movimiento.*, temporal_1.descrip AS proveedor, entsal.cod_up, vehiculo.nro_patente, vehiculo.marca, CONCAT(REPLACE(unipresu.codigo, '-', ''), ' - ', unipresu.nombre) AS up FROM (((movimiento INNER JOIN ";
		$sql.= "(";
		$sql.= "SELECT";
		$sql.= "  0 AS id_proveedor";
		$sql.= ", 'Parque Automotor' AS descrip";
		$sql.= ") AS temporal_1";
	$sql.= " USING(id_proveedor)) INNER JOIN entsal USING(id_entsal)) INNER JOIN vehiculo USING(id_vehiculo)) LEFT JOIN unipresu USING(cod_up))";
	$sql.= ") AS temporal_2";
	$sql.= " WHERE estado='S'";
	
	if (isset($_REQUEST['cod_up'])) $sql.= " AND cod_up=" . $_REQUEST['cod_up'];
	if (! is_null($_REQUEST['desde'])) $sql.= " AND DATE(f_sal) >= '" . $_REQUEST['desde'] . "'";
	if (! is_null($_REQUEST['hasta'])) $sql.= " AND DATE(f_sal) <= '" . $_REQUEST['hasta'] . "'";
	
	$sql.= " ORDER BY f_ent DESC";
	
	$rs = $mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		$row->total = (float) $row->total;
		$total+= $row->total;
		?>
		<tr>
		<td><?php echo $row->nro_patente . "  " . $row->marca; ?></td>
		<td><?php echo $row->id_movimiento; ?></td>
		<td><?php echo $row->proveedor; ?></td>
		<?php
		if (is_null($_REQUEST['cod_up'])) {
			?>
			<td><?php echo $row->up; ?></td>
			<?php
		}
		?>
		<td><?php echo $row->f_sal; ?></td>
		<td align="right"><?php echo number_format($row->total, 2, ",", "."); ?></td>
		</tr>
		<?php
	}
	?>
	
	<?php
	if (! isset($_REQUEST['cod_up'])) {
		?>
		<tr><td colspan="6" align="right"><?php echo number_format($total, 2, ",", "."); ?></td></tr>
		<?php
	} else {
		?>
		<tr><td colspan="5" align="right"><?php echo number_format($total, 2, ",", "."); ?></td></tr>
		<?php
	}
	?>
	

	</tbody>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "incidentes" : {
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Incidentes</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>LISTADO INCIDENTES</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php
	if (! is_null($_REQUEST['id_chofer'])) {

	} else if (! is_null($_REQUEST['id_tipo_incidente'])) {
		$sql = "SELECT descrip FROM tipo_incidente WHERE id_tipo_incidente=" . $_REQUEST['id_tipo_incidente'];
		$rsAux = $mysqli->query($sql);
		$rowAux = $rsAux->fetch_object();
		
		?>
		<tr><td align="center" colspan="10"><big>Tipo incidente: <?php echo $rowAux->descrip; ?></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
	} else if (! is_null($_REQUEST['organismo_area_id'])) {
		$sql = "SELECT";
		$sql.= "  CONCAT(_organismos_areas.organismo_area, ' (', _organismos.organismo, ')') AS label";
		$sql.= "  , _organismos_areas.organismo_area_id AS model";
		$sql.= " FROM (_organismos_areas INNER JOIN _organismos USING(organismo_id))";
		$sql.= " WHERE _organismos_areas.organismo_area_id='" . $_REQUEST['organismo_area_id'] . "'";
		
		$rsAux = $mysqli2->query($sql);
		if ($rsAux->num_rows > 0) {
			$rowAux = $rsAux->fetch_object();
			$rowAux = $rowAux->label;
		}
		
		?>
		<tr><td align="center" colspan="10"><big>Uni.presu.: <?php echo $rowAux; ?></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
	}
	?>
	<tr><td align="center" colspan="6"><big><?php
		if (! is_null($_REQUEST['desde']) && ! is_null($_REQUEST['hasta'])) {
			echo "Período: " . $_REQUEST['desde'] . " / " . $_REQUEST['hasta'];
		} else if (! is_null($_REQUEST['desde'])) {
			echo "Período: desde " . $_REQUEST['desde'];
		} else if (! is_null($_REQUEST['hasta'])) {
			echo "Período: hasta " . $_REQUEST['hasta'];
		}
	?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<tr>
	<td colSpan="10">
	<table border="1" cellpadding="5" cellspacing="0" width="100%" align="center">
	<thead>
	<tr><th>Chofer</th><th></th><th>Descripción</th></tr>
	</thead>
	<tbody>

	<?php
	
	
	$sql = "SELECT incidente.*, chofer.apenom, chofer.dni, tipo_incidente.descrip AS tipo_incidente_descrip FROM (incidente INNER JOIN chofer USING(id_chofer)) INNER JOIN tipo_incidente USING(id_tipo_incidente)";
	$sql.= " WHERE TRUE";
	if (! is_null($_REQUEST['id_chofer'])) $sql.= " AND incidente.id_chofer=" . $_REQUEST['id_chofer'];
	if (! is_null($_REQUEST['id_tipo_incidente'])) $sql.= " AND incidente.id_tipo_incidente=" . $_REQUEST['id_tipo_incidente'];
	if (! is_null($_REQUEST['organismo_area_id'])) $sql.= " AND chofer.organismo_area_id='" . $_REQUEST['organismo_area_id'] . "'";
	if (! is_null($_REQUEST['desde'])) $sql.= " AND DATE(fecha) >= '" . $_REQUEST['desde'] . "'";
	if (! is_null($_REQUEST['hasta'])) $sql.= " AND DATE(fecha) <= '" . $_REQUEST['hasta'] . "'";
	$sql.= " ORDER BY fecha DESC, apenom";
	
	
	$rs = $mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		?>
		<tr><td rowSpan="3"><?php echo $row->apenom . " - " . $row->dni; ?></td><td><?php echo $row->fecha; ?></td><td rowSpan="3"><?php echo nl2br($row->descrip); ?></td></tr>
		<tr><td><?php echo $row->tipo_incidente_descrip; ?></td></tr>
		<tr><td><?php echo $row->id_usuario; ?></td></tr>
		<?php
	}
	

	?>
	
	</tbody>
	</table>
	</td>
	</tr>
	

	</table>
	</body>
	</html>
	<?php
	
break;
}



case "choferes" : {
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Choferes</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>LISTADO DE CHOFERES</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php
	if (isset($_REQUEST['organismo_area_id'])) {
		$sql = "SELECT";
		$sql.= "  CONCAT(_organismos_areas.organismo_area, ' (', _organismos.organismo, ')') AS label";
		$sql.= "  , _organismos_areas.organismo_area_id AS model";
		$sql.= " FROM (_organismos_areas INNER JOIN _organismos USING(organismo_id))";
		$sql.= " WHERE _organismos_areas.organismo_area_id='" . $_REQUEST['organismo_area_id'] . "'";
		
		$rsAux = $mysqli2->query($sql);
		if ($rsAux->num_rows > 0) {
			$rowAux = $rsAux->fetch_object();
			$rowAux = $rowAux->label;
		}
		
		?>
		<tr><td align="center" colspan="10"><big>Uni.presu.: <?php echo $rowAux; ?></big></td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
	}
	?>
	<tr><td>&nbsp;</td></tr>
	
	<tr>
	<td colSpan="10">
	<table border="1" cellpadding="5" cellspacing="0" width="100%" align="center">
	<thead>
	<tr><th>Apellido/Nombre</th><th>DNI</th><th>Uni.presu.</th><th>Lic.emi.</th><th>Lic.ven.</th><th>Teléfono</th><th>Obs.</th></tr>
	</thead>
	<tbody>

	<?php
	
	
	$sql = "SELECT * FROM chofer";
	$sql.= " WHERE TRUE";
	if (isset($_REQUEST['organismo_area_id'])) $sql.= " AND organismo_area_id='" . $_REQUEST['organismo_area_id'] . "'";
	$sql.= " ORDER BY apenom";
	
	
	$rs = $mysqli->query($sql);
	while ($row = $rs->fetch_object()) {
		/*
 
		?>
		<tr><td rowSpan="3"><?php echo $row->apenom . " - " . $row->dni; ?></td><td><?php echo $row->fecha; ?></td><td rowSpan="3"><?php echo nl2br($row->descrip); ?></td></tr>
		<tr><td><?php echo $row->tipo_incidente_descrip; ?></td></tr>
		<tr><td><?php echo $row->id_usuario; ?></td></tr>
		<?php

		*/
		
		$sql = "SELECT";
		$sql.= "  CONCAT(_organismos_areas.organismo_area, ' (', _organismos.organismo, ')') AS label";
		$sql.= "  , _organismos_areas.organismo_area_id AS model";
		$sql.= " FROM (_organismos_areas INNER JOIN _organismos USING(organismo_id))";
		$sql.= " WHERE _organismos_areas.organismo_area_id='" . $row->organismo_area_id . "'";
		
		$rsAux = $mysqli2->query($sql);
		if ($rsAux->num_rows > 0) {
			$rowAux = $rsAux->fetch_object();
			$rowAux = $rowAux->label;
		} else {
			$rowAux = "";
		}
		
		?>
		<tr><td><?php echo $row->apenom; ?></td><td><?php echo $row->dni; ?></td><td><?php echo $rowAux; ?></td><td><?php echo $row->f_emision; ?></td><td><?php echo $row->f_vencimiento; ?></td><td><?php echo $row->telefono; ?></td><td><?php echo $row->observa; ?></td></tr>
		<?php
	}
	

	?>
	
	</tbody>
	</table>
	</td>
	</tr>
	

	</table>
	</body>
	</html>
	<?php
	
break;
}


	
case "historial" : {
	
	$sql = "SELECT * FROM vehiculo WHERE id_vehiculo=" . $_REQUEST['id_vehiculo'];
	$rsVehiculo = $mysqli->query($sql);
	$rowVehiculo = $rsVehiculo->fetch_object();
	
	$sql = "SELECT";
	$sql.= " descrip AS label";
	$sql.= " FROM " . $inventario . ".uni_presu";
	$sql.= " WHERE id_uni_presu='" . $rowVehiculo->id_uni_presu . "'";
	
	$rsUni_presu = $mysqli->query($sql);
	if ($rsUni_presu->num_rows > 0) {
		$rowUni_presu = $rsUni_presu->fetch_object();
		$rowVehiculo->uni_presu = $rowUni_presu->label;
	} else {
		$rowVehiculo->uni_presu = "";
	}
	
	
	?>
	
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Historial</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><b>Historial Vehiculo: <?php echo $rowVehiculo->nro_patente . "  " . $rowVehiculo->marca; ?></b></td></tr>
	<tr><td colspan="20">Uni.presu.: <?php echo $rowVehiculo->uni_presu; ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>Usuario: <?php echo $_SESSION['login']->usuario; ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<?php
	
	$sql = "SELECT * FROM entsal WHERE estado<>'A' AND id_vehiculo=" . $rowVehiculo->id_vehiculo . " ORDER BY f_ent DESC";
	$rsEntsal = $mysqli->query($sql);
	
	while ($rowEntsal = $rsEntsal->fetch_object()) {
		?>
		<tr><td colspan="20"><hr/></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
		<td><?php echo "Entrada: " . $rowEntsal->f_ent; ?></td>
		<td><?php echo "Salida: " . $rowEntsal->f_sal; ?></td>
		<td><?php echo "Km: " . number_format($rowEntsal->kilo, 0, ",", "."); ?></td>
		<td align="right"><?php echo "Total: " . number_format($rowEntsal->total, 2, ",", "."); ?></td>
		</tr>
		<?php
		
		
		
		$sql = "SELECT * FROM(";
		$sql.= "(SELECT movimiento.*, proveedor.descrip AS proveedor FROM movimiento INNER JOIN " . $inventario . ".proveedor USING(id_proveedor))";
		$sql.= " UNION ALL";
		$sql.= "(SELECT movimiento.*, temporal_1.descrip AS proveedor FROM movimiento INNER JOIN ";
			$sql.= "(";
			$sql.= "SELECT";
			$sql.= "  0 AS id_proveedor";
			$sql.= ", 'Parque Automotor' AS descrip";
			$sql.= ") AS temporal_1";
		$sql.= " USING(id_proveedor))";
		$sql.= ") AS temporal_2";
		$sql.= " WHERE estado<>'A' AND id_entsal=" . $rowEntsal->id_entsal;
		$sql.= " ORDER BY f_ent DESC";
		
		
		$rsMovimiento = $mysqli->query($sql);
		
		while ($rowMovimiento = $rsMovimiento->fetch_object()) {
			?>
			<tr><td>&nbsp;</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan="20"><?php echo "# " . $rowMovimiento->id_movimiento . " - " . $rowMovimiento->proveedor; ?></td></tr>
			<tr>
			<td><?php echo "Entrada: " . $rowMovimiento->f_ent; ?></td>
			<td><?php echo "Salida: " . $rowMovimiento->f_sal; ?></td>
			<td><?php echo "Km: " . number_format($rowMovimiento->kilo, 0, ",", "."); ?></td>
			<td align="right"><?php echo "Total: " . number_format($rowMovimiento->total, 2, ",", "."); ?></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan="20">
			<table border="1" rules="all" cellpadding="1" cellspacing="0" width="100%" align="center">
			<tr><th>Tipo reparación</th><th align="right">Costo</th><th align="right">Cant.</th><th align="right">Total</th></tr>
			<?php
			//$sql = "SELECT * FROM reparacion WHERE id_movimiento=" . $rowMovimiento->id_movimiento;
			$sql = "SELECT reparacion.*, tipo_reparacion.descrip AS tipo_reparacion FROM reparacion INNER JOIN tipo_reparacion USING(id_tipo_reparacion) WHERE id_movimiento=" . $rowMovimiento->id_movimiento;
			$rsReparacion = $mysqli->query($sql);
			
			while ($rowReparacion = $rsReparacion->fetch_object()) {
				?>
				<tr>
				<td><?php echo $rowReparacion->tipo_reparacion; ?></td>
				<td align="right"><?php echo number_format($rowReparacion->costo, 2, ",", "."); ?></td>
				<td align="right"><?php echo $rowReparacion->cantidad; ?></td>
				<td align="right"><?php echo number_format($rowReparacion->total, 2, ",", "."); ?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<?php
		}
		
		?>
		
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		
		<?php
		
	}

	?>

	<tr><td colspan="20"><hr/></td></tr>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}
	
	
case "salida_vehiculo" : {

	$sql = "SELECT entsal.*, vehiculo.*, responsable.apenom FROM entsal INNER JOIN vehiculo USING(id_vehiculo) INNER JOIN responsable USING(id_responsable) WHERE id_entsal=" . $_REQUEST['id_entsal'];
	$rsEntsal = $mysqli->query($sql);
	$rowEntsal = $rsEntsal->fetch_object();
	
	$sql = "SELECT";
	$sql.= " descrip AS label";
	$sql.= " FROM " . $inventario . ".uni_presu";
	$sql.= " WHERE id_uni_presu='" . $rowEntsal->id_uni_presu . "'";
	
	$rsUni_presu = $mysqli->query($sql);
	if ($rsUni_presu->num_rows > 0) {
		$rowUni_presu = $rsUni_presu->fetch_object();
		$rowEntsal->uni_presu = $rowUni_presu->label;
	} else {
		$rowEntsal->uni_presu = "";
	}
	
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Conformidad</title>
	</head>
	<body>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td colspan="10" align="center"> <img src="../../../resource/vehiculos/logo.jpg" width="70"></td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de la Capital</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><b>Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>FORMULARIO DE CONFORMIDAD</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><b>Vehículo: <?php echo $rowEntsal->nro_patente . "  " . $rowEntsal->marca; ?></b></td><td>Salida: <?php echo $rowEntsal->f_sal; ?></td><td>Km: <?php echo $rowEntsal->kilo; ?></td></tr>
	<tr><td colspan="20">Uni.presu.: <?php echo $rowEntsal->uni_presu; ?></td></tr>
	<tr><td>Responsable: <?php echo $rowEntsal->apenom; ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<?php
	
	
	
	
	$sql = "SELECT * FROM(";
	$sql.= "(SELECT movimiento.*, proveedor.descrip AS proveedor FROM movimiento INNER JOIN " . $inventario . ".proveedor USING(id_proveedor))";
	$sql.= " UNION ALL";
	$sql.= "(SELECT movimiento.*, temporal_1.descrip AS proveedor FROM movimiento INNER JOIN ";
		$sql.= "(";
		$sql.= "SELECT";
		$sql.= "  0 AS id_proveedor";
		$sql.= ", 'Parque Automotor' AS descrip";
		$sql.= ") AS temporal_1";
	$sql.= " USING(id_proveedor))";
	$sql.= ") AS temporal_2";

	if (! isset($_REQUEST['id_movimiento'])) {
		$sql.= " WHERE id_entsal=" . $rowEntsal->id_entsal . " AND estado='S'";
	} else {
		$sql.= " WHERE id_movimiento=" . $_REQUEST['id_movimiento'];
	}
	
	$sql.= " ORDER BY f_ent DESC";
	
	
	$rsMovimiento = $mysqli->query($sql);
	
	while ($rowMovimiento = $rsMovimiento->fetch_object()) {
		?>
		<tr><td colspan="2"><?php echo "# " .  $rowMovimiento->id_movimiento . " - " . $rowMovimiento->proveedor; ?></td><td>Km: <?php echo $rowMovimiento->kilo; ?></td></tr>
		<tr><td colspan="20">
		<table border="1" rules="all" cellpadding="1" cellspacing="0" width="100%" align="center">
		<tr><th>Tipo reparación</th><th>Observaciones</th><th align="right">Costo</th><th align="right">Cant.</th><th align="right">Total</th></tr>
		<?php
		//$sql = "SELECT * FROM reparacion WHERE id_movimiento=" . $rowMovimiento->id_movimiento;
		$sql = "SELECT reparacion.*, tipo_reparacion.descrip AS tipo_reparacion FROM reparacion INNER JOIN tipo_reparacion USING(id_tipo_reparacion) WHERE id_movimiento=" . $rowMovimiento->id_movimiento;
		$rsReparacion = $mysqli->query($sql);
		
		while ($rowReparacion = $rsReparacion->fetch_object()) {
			?>
			<tr>
			<td><?php echo $rowReparacion->tipo_reparacion; ?></td>
			<td><?php echo $rowReparacion->observa; ?></td>
			<td align="right"><?php echo number_format($rowReparacion->costo, 2, ",", "."); ?></td>
			<td align="right"><?php echo $rowReparacion->cantidad; ?></td>
			<td align="right"><?php echo number_format((float) $rowReparacion->total, 2, ",", "."); ?></td>
			</tr>
			<?php
		}
		?>
		<tr>
		<td colspan="5" align="right"><?php echo number_format((float) $rowMovimiento->total, 2, ",", "."); ?></td>
		</tr>
		</table>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php
	}
	?>

	<tr><td>&nbsp;</td></tr>
	<tr><td>_____________________________</td><td>_____________________________</td></tr>
	<tr><td>Firma usuario</td><td>Firma responsable uni.presu.</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>_____________________________</td></tr>
	<tr><td>Firma responsable traslado</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><b>Usuario: </b><?php echo $_SESSION['login']->usuario; ?></td></tr>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "entrada_taller" : {

	$sql = "SELECT * FROM entsal INNER JOIN vehiculo USING(id_vehiculo) WHERE id_entsal=" . $_REQUEST['id_entsal'];
	$rsEntsal = $mysqli->query($sql);
	$rowEntsal = $rsEntsal->fetch_object();
	
	$sql = "SELECT";
	$sql.= " descrip AS label";
	$sql.= " FROM " . $inventario . ".uni_presu";
	$sql.= " WHERE id_uni_presu='" . $rowEntsal->id_uni_presu . "'";
	
	$rsUni_presu = $mysqli->query($sql);
	if ($rsUni_presu->num_rows > 0) {
		$rowUni_presu = $rsUni_presu->fetch_object();
		$rowEntsal->uni_presu = $rowUni_presu->label;
	} else {
		$rowEntsal->uni_presu = "";
	}
	
	
	
	$sql = "SELECT * FROM(";
	$sql.= "(SELECT movimiento.*, proveedor.descrip AS proveedor FROM movimiento INNER JOIN " . $inventario . ".proveedor USING(id_proveedor))";
	$sql.= " UNION ALL";
	$sql.= "(SELECT movimiento.*, temporal_1.descrip AS proveedor FROM movimiento INNER JOIN ";
		$sql.= "(";
		$sql.= "SELECT";
		$sql.= "  0 AS id_proveedor";
		$sql.= ", 'Parque Automotor' AS descrip";
		$sql.= ") AS temporal_1";
	$sql.= " USING(id_proveedor))";
	$sql.= ") AS temporal_2";
	$sql.= " WHERE id_movimiento=" . $_REQUEST['id_movimiento'];
	$sql.= " ORDER BY f_ent DESC";
	
	
	$rsMovimiento = $mysqli->query($sql);
	$rowMovimiento = $rsMovimiento->fetch_object();
	
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Orden trabajo</title>
	</head>
	<body>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td colspan="10" align="center"> <img src="../../../resource/vehiculos/logo.jpg" width="70"></td></tr>
	<tr><td align="center" colspan="6"><big><b>Municipalidad de la Capital</b></big></td></tr>
	<tr><td align="center" colspan="6"><big><b>Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="6"><big><b>ORDEN DE TRABAJO # <?php echo $_REQUEST['id_movimiento']; ?></b></big></td></tr>
	<tr><td align="center" colspan="6"><big><?php echo date("d/m/Y H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><b>Vehiculo: <?php echo $rowEntsal->nro_patente . "  " . $rowEntsal->marca; ?></b></td><td>Entrada: <?php $aux = new DateTime($rowMovimiento->f_ent); echo $aux->format("d/m/Y H:i:s"); ?></td></tr>
	<tr><td colspan="20">Uni.presu.: <?php echo $rowEntsal->uni_presu; ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<?php
	
		?>
		<tr><td colspan="20">Sres.</td></tr>
		<tr><td colspan="20"><?php echo $rowMovimiento->proveedor; ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="20"><b>Se solicita la ejecución del siguiente trabajo:</b></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="20"><?php echo nl2br($rowMovimiento->observa); ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<?php

	?>

	<tr><td>&nbsp;</td></tr>
	<tr><td>_____________________________</td><td>_____________________________</td></tr>
	<tr><td>Firma usuario</td><td>Firma jefe parque automotor</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>_____________________________</td></tr>
	<tr><td>Firma responsable traslado</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td><b>Usuario: </b><?php echo $_SESSION['login']->usuario; ?></td></tr>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}



	
	
case "comprobante_entrega" : {
	
	$sql = "SELECT entrega_lugar.descrip AS lugar, entrega.descrip, entrega.fecha FROM entrega INNER JOIN entrega_lugar USING(id_entrega_lugar) WHERE id_entrega=" . $_REQUEST['id_entrega'];
	$rsEntrega = $mysqli->query($sql);
	$rowEntrega = $rsEntrega->fetch_object();

	$sql = "SELECT producto.descrip, stock.lote, stock.f_vencimiento, entrega_item.cantidad FROM ((entrega INNER JOIN entrega_item USING(id_entrega)) INNER JOIN stock USING(id_stock)) INNER JOIN producto ON stock.id_producto = producto.id_producto WHERE entrega.id_entrega=" . $_REQUEST['id_entrega'] . " ORDER BY descrip, f_vencimiento";
	$rsEntrega_item = $mysqli->query($sql);
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Comprobante de entrega</title>
	</head>
	<body>
	<table border="0" width="700" align="center">
	<tr><td align="center" colspan="2"><big>COMPROBANTE DE ENTREGA</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>Destino: </td><td><?php echo $rowEntrega->lugar; ?></td></tr>
	<tr><td>Descripción: </td><td><?php echo $rowEntrega->descrip; ?></td></tr>
	<tr><td>Fecha: </td><td><?php echo $rowEntrega->fecha; ?></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" width="100%" align="center">
	<tr><th>Producto</th><th>Lote</th><th>F.vencimiento</th><th align="right">Cantidad</th></tr>
	<?php
	while ($rowEntrega_item = $rsEntrega_item->fetch_object()) {
		?>
		<tr><td><?php echo $rowEntrega_item->descrip; ?></td><td><?php echo $rowEntrega_item->lote; ?></td><td><?php echo $rowEntrega_item->f_vencimiento; ?></td><td align="right"><?php echo $rowEntrega_item->cantidad; ?></td></tr>
		<?php
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}
	
	
case "consumo_producto" : {

	$sql = "SELECT producto.id_producto, producto.descrip, SUM(entrega_item.cantidad) AS cantidad FROM (entrega INNER JOIN entrega_item USING(id_entrega)) INNER JOIN producto USING(id_producto) WHERE entrega.fecha BETWEEN '" . $_REQUEST['desde'] . "' AND '" . $_REQUEST['hasta'] . "' GROUP BY id_producto ORDER BY descrip";
	$rs = $mysqli->query($sql);
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado Consumo x Producto</title>
	</head>
	<body>
	<table border="0" width="700" align="center">
	<tr><td align="center" colspan="6"><big><?php echo date('Y-m-d') ?> - LISTADO CONSUMO x PRODUCTO</big></td></tr>
	<tr><td align="center" colspan="6"><big>(período <?php echo substr($_REQUEST['desde'], 0, 10) . " / " . substr($_REQUEST['hasta'], 0, 10) ?>)</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" width="100%" align="center">
	<tr><th>Producto</th><th align="right">Cantidad</th></tr>
	<?php
	while ($row = $rs->fetch_array()) {
		?>
		<tr><td><?php echo $row['descrip']; ?></td><td align="right"><?php echo $row['cantidad']; ?></td></tr>
		<?php
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "stock" : {

	$sql = "SELECT producto.*, SUM(stock.stock) AS cantidad FROM producto LEFT JOIN stock USING(id_producto) GROUP BY id_producto ORDER BY descrip";
	$rs = $mysqli->query($sql);
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado de Stock</title>
	</head>
	<body>
	<table border="0" width="700" align="center">
	<tr><td align="center" colspan="6"><big><?php echo date('Y-m-d') ?> - LISTADO DE STOCK</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" width="100%" align="center">
	<tr><th>Producto</th><th align="right">Pto.reposición</th><th align="right">Stock</th></tr>
	<?php
	while ($row = $rs->fetch_array()) {
		?>
		<tr><td><?php echo $row['descrip']; ?></td><td align="right"><?php echo $row['pto_reposicion']; ?></td><td align="right"><?php echo (($row['cantidad'] == null) ? 0: $row['cantidad']); ?></td></tr>
		<?php
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "producto_falta" : {

	$sql = "SELECT producto.*, SUM(stock.stock) AS cantidad FROM producto LEFT JOIN stock USING(id_producto) GROUP BY id_producto HAVING cantidad <= pto_reposicion OR ISNULL(cantidad) ORDER BY descrip";
	$rs = $mysqli->query($sql);
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Listado Producto en Falta</title>
	</head>
	<body>
	<table border="0" width="700" align="center">
	<tr><td align="center" colspan="6"><big><?php echo date('Y-m-d') ?> - LISTADO PRODUCTO EN FALTA</big></td></tr>
	<tr><td>&nbsp;</td></tr>
	
	<tr><td colspan="20">
	<table border="1" rules="all" width="100%" align="center">
	<tr><th>Producto</th><th align="right">Pto.reposición</th><th align="right">Stock</th></tr>
	<?php
	while ($row = $rs->fetch_array()) {
		?>
		<tr><td><?php echo $row['descrip']; ?></td><td align="right"><?php echo $row['pto_reposicion']; ?></td><td align="right"><?php echo (($row['cantidad'] == null) ? 0: $row['cantidad']); ?></td></tr>
		<?php
	}
	?>
	</table>
	</td></tr>
	</table>
	</body>
	</html>
	<?php
	
break;
}



case "vehiculos" : {
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Vehículos</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="10"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="10"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="10"><big><b>LISTADO DE VEHÍCULOS</b></big></td></tr>
	<tr><td align="center" colspan="10"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php
	
	if (isset($_REQUEST['id_uni_presu'])) {
		$sql = "SELECT";
		$sql.= "  *";
		$sql.= " FROM " . $inventario . ".uni_presu";
		$sql.= " WHERE id_uni_presu=" . $_REQUEST['id_uni_presu'];
		
		$rsAux = $mysqli->query($sql);
		$rowAux = $rsAux->fetch_object();
		
		?>
		<tr><td align="center" colspan="6"><big><b><?php echo "Uni.presu.: " . $rowAux->descrip; ?></b></big></td></tr>
		<?php
	}
	if (isset($_REQUEST['id_tipo_vehiculo'])) {
		$sql = "SELECT";
		$sql.= "  *";
		$sql.= " FROM tipo_vehiculo";
		$sql.= " WHERE id_tipo_vehiculo=" . $_REQUEST['id_tipo_vehiculo'];
		
		$rsAux = $mysqli->query($sql);
		$rowAux = $rsAux->fetch_object();
		
		?>
		<tr><td align="center" colspan="6"><big><b><?php echo "Tipo vehículo: " . $rowAux->descrip; ?></b></big></td></tr>
		<?php
	}
	if (isset($_REQUEST['departamento_id'])) {
		$sql = "SELECT";
		$sql.= "  *";
		$sql.= " FROM _departamentos";
		$sql.= " WHERE departamento_id=" . $_REQUEST['departamento_id'];
		
		$rsAux = $mysqli2->query($sql);
		$rowAux = $rsAux->fetch_object();
		
		?>
		<tr><td align="center" colspan="6"><big><b><?php echo "Departamento: " . $rowAux->departamento; ?></b></big></td></tr>
		<?php
	}
	
	?>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php
	
	$sql = "SELECT";
	$sql.= " DISTINCTROW";
	$sql.= "  uni_presu.*";
	$sql.= " FROM " . $inventario . ".uni_presu INNER JOIN vehiculo USING(id_uni_presu)";
	if (isset($_REQUEST['id_uni_presu'])) {
		$sql.= " WHERE uni_presu.id_uni_presu=" . $_REQUEST['id_uni_presu'];
	}
	$sql.= " ORDER BY descrip";
	
	$rsUni_presu = $mysqli->query($sql);
	while ($rowUni_presu = $rsUni_presu->fetch_object()) {
		?>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td align="center" colspan="6"><?php echo "Uni.presu.: " . $rowUni_presu->descrip; ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="10"><hr></td></tr>
		
		<tr><td align="center" colspan="10">
		<table border="0" cellpadding="5" cellspacing="0" width="100%" align="center">
		
		<?php
		
		$sql = "SELECT";
		$sql.= "  vehiculo.*";
		$sql.= "  , tipo_vehiculo.descrip AS tipo_vehiculo_descrip";
		$sql.= "  , uni_presu.descrip AS uni_presu_descrip";
		$sql.= "  , depositario.descrip AS depositario_descrip";
		$sql.= "  , responsable.apenom";
		//$sql.= "  , CONCAT(_localidades.localidad, ' (', _departamentos.departamento, ')') AS localidad_descrip";
		$sql.= " FROM vehiculo INNER JOIN tipo_vehiculo USING(id_tipo_vehiculo)";
		$sql.= "  INNER JOIN " . $inventario . ".uni_presu USING(id_uni_presu)";
		$sql.= "  INNER JOIN depositario USING(id_depositario)";
		$sql.= "  INNER JOIN responsable USING(id_responsable)";
		//$sql.= "  LEFT JOIN (_localidades INNER JOIN _departamentos USING(departamento_id)) ON vehiculo.localidad_id = _localidades.localidad_id COLLATE utf8_spanish_ci";
		$sql.= " WHERE id_uni_presu=" . $rowUni_presu->id_uni_presu;
		if (isset($_REQUEST['id_tipo_vehiculo'])) {
			$sql.= " AND vehiculo.id_tipo_vehiculo=" . $_REQUEST['id_tipo_vehiculo'];
		}
		$sql.= " ORDER BY nro_patente, nro_chasis, marca";
		
		$rsVehiculo = $mysqli->query($sql);
		while ($rowVehiculo = $rsVehiculo->fetch_object()) {
			$sql = "SELECT";
			$sql.= " localidad_id, departamento_id, CONCAT(_localidades.localidad, ' (', _departamentos.departamento, ')') AS localidad_descrip";
			$sql.= " FROM _localidades INNER JOIN _departamentos USING(departamento_id)";
			$sql.= " WHERE _localidades.localidad_id='" . $rowVehiculo->localidad_id . "'";
			
			$rsLocalidad = $mysqli2->query($sql);
			if ($rsLocalidad->num_rows > 0) {
				$rowLocalidad = $rsLocalidad->fetch_object();
				
				$rowVehiculo->localidad_descrip = $rowLocalidad->localidad_descrip;
			} else {
				$rowLocalidad = new stdClass;
				$rowLocalidad->departamento_id = null;
			}

			if (isset($_REQUEST['departamento_id'])) {
				if ($rowLocalidad->departamento_id != $_REQUEST['departamento_id']) continue;
			}			
			
			
			?>

			<tr><td><?php echo "Nro.patente: " . $rowVehiculo->nro_patente; ?></td><td><?php echo "Nro.chasis: " . $rowVehiculo->nro_chasis; ?></td><td><?php echo "Marca: " . $rowVehiculo->marca; ?></td></tr>
			<tr><td><?php echo "Tipo: " . $rowVehiculo->tipo_vehiculo_descrip; ?></td><td><?php echo "Modelo: " . $rowVehiculo->modelo; ?></td><td><?php echo "Nro.motor: " . $rowVehiculo->nro_motor; ?></td></tr>
			<tr><td><?php echo "Obs.: " . $rowVehiculo->observa; ?></td><td><?php echo "Nro.seg./pol.: " . $rowVehiculo->nro_poliza; ?></td><td><?php echo "Localidad: " . $rowVehiculo->localidad_descrip; ?></td></tr>
			<tr><td><?php echo "Uni.presu.: " . $rowVehiculo->uni_presu_descrip; ?></td><td><?php echo "Depositario: " . $rowVehiculo->depositario_descrip; ?></td><td><?php echo "Responsable: " . $rowVehiculo->apenom; ?></td></tr>
			<tr><td colspan="10"><hr></td></tr>
			
			<?php
		}
		
		?>
		
		</table>
		</td></tr>
		<tr><td>&nbsp;</td></tr>
		
		<?php
	}
		
	?>
	</table>
	</body>
	</html>
	<?php
	
break;
}




case "vehiculos" : {
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Vehículos</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="0" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="10"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="10"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="10"><big><b>LISTADO DE VEHÍCULOS</b></big></td></tr>
	<tr><td align="center" colspan="10"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php
	
	$sql = "SELECT";
	$sql.= " DISTINCTROW";
	$sql.= "  uni_presu.*";
	$sql.= " FROM " . $inventario . ".uni_presu INNER JOIN vehiculo USING(id_uni_presu)";
	$sql.= " ORDER BY descrip";
	
	$rsUni_presu = $mysqli->query($sql);
	while ($rowUni_presu = $rsUni_presu->fetch_object()) {
		?>
		<tr><td>&nbsp;</td></tr>
		<tr><td align="center" colspan="6"><?php echo "Uni.presu.: " . $rowUni_presu->descrip; ?></td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="10"><hr></td></tr>
		
		<tr><td align="center" colspan="10">
		<table border="0" cellpadding="5" cellspacing="0" width="100%" align="center">
		
		<?php
		
		$sql = "SELECT";
		$sql.= "  vehiculo.*";
		$sql.= "  , tipo_vehiculo.descrip AS tipo_vehiculo_descrip";
		$sql.= "  , uni_presu.descrip AS uni_presu_descrip";
		$sql.= "  , depositario.descrip AS depositario_descrip";
		$sql.= "  , responsable.apenom";
		$sql.= " FROM vehiculo INNER JOIN tipo_vehiculo USING(id_tipo_vehiculo)";
		$sql.= "  INNER JOIN " . $inventario . ".uni_presu USING(id_uni_presu)";
		$sql.= "  INNER JOIN depositario USING(id_depositario)";
		$sql.= "  INNER JOIN responsable USING(id_responsable)";
		$sql.= " WHERE id_uni_presu=" . $rowUni_presu->id_uni_presu;
		$sql.= " ORDER BY nro_patente";
		
		$rsVehiculo = $mysqli->query($sql);
		while ($rowVehiculo = $rsVehiculo->fetch_object()) {
			$sql = "SELECT";
			$sql.= " CONCAT(_localidades.localidad, ' (', _departamentos.departamento, ')') AS localidad_descrip";
			$sql.= " FROM _localidades INNER JOIN _departamentos USING(departamento_id)";
			$sql.= " WHERE _localidades.localidad_id='" . $rowVehiculo->localidad_id . "'";
			
			$rsAux = $mysqli2->query($sql);
			if ($rsAux->num_rows > 0) {
				$rowAux = $rsAux->fetch_object();
				
				$rowVehiculo->localidad_descrip = $rowAux->localidad_descrip;
			}
			
			
			?>

			<tr><td><?php echo "Patente: " . $rowVehiculo->nro_patente; ?></td><td><?php echo "Marca: " . $rowVehiculo->marca; ?></td><td><?php echo "Tipo: " . $rowVehiculo->tipo_vehiculo_descrip; ?></td></tr>
			<tr><td><?php echo "Modelo: " . $rowVehiculo->modelo; ?></td><td><?php echo "Nro.motor: " . $rowVehiculo->nro_motor; ?></td><td><?php echo "Nro.chasis: " . $rowVehiculo->nro_chasis; ?></td></tr>
			<tr><td><?php echo "Obs.: " . $rowVehiculo->observa; ?></td><td><?php echo "Nro.seg./pol.: " . $rowVehiculo->nro_poliza; ?></td><td><?php echo "Localidad: " . $rowVehiculo->localidad_descrip; ?></td></tr>
			<tr><td><?php echo "Uni.presu.: " . $rowVehiculo->uni_presu_descrip; ?></td><td><?php echo "Depositario: " . $rowVehiculo->depositario_descrip; ?></td><td><?php echo "Responsable: " . $rowVehiculo->apenom; ?></td></tr>
			<tr><td colspan="10"><hr></td></tr>
			
			<?php
		}
		
		?>
		
		</table>
		</td></tr>
		<tr><td>&nbsp;</td></tr>
		
		<?php
	}
		
	?>
	</table>
	</body>
	</html>
	<?php
	
break;
}


case "responsables" : {
	
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<title>Responsables</title>
	</head>
	<body>
	<input type="submit" value="Imprimir" onClick="window.print();"/>
	<table border="0" cellpadding="5" cellspacing="0" width="800" align="center">
	<tr><td align="center" colspan="10"><big><b>Dirección de Compras - Parque Automotor</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="10"><big><b>Municipalidad de Santiago del Estero</b></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td align="center" colspan="10"><big><b>LISTADO DE RESPONSABLES</b></big></td></tr>
	<tr><td align="center" colspan="10"><big><?php echo date("Y-m-d H:i:s"); ?></big></td></tr>
	<tr><td>&nbsp;</td></tr>
	<?php
	
	if (isset($_REQUEST['id_responsable'])) {
		$sql = "SELECT";
		$sql.= "  *";
		$sql.= " FROM responsable";
		$sql.= " WHERE id_responsable=" . $_REQUEST['id_responsable'];
		
		$rsAux = $mysqli->query($sql);
		$rowAux = $rsAux->fetch_object();
		
		?>
		<tr><td align="center" colspan="6"><big><b><?php echo "Responsable: " . $rowAux->apenom; ?></b></big></td></tr>
		<?php
	}
	
	?>
	<tr><td>&nbsp;</td></tr>
	<tr><td colspan="10"><hr></td></tr>
	<?php

	
	$sql = "SELECT";
	$sql.= " *";
	$sql.= " FROM responsable";
	if (isset($_REQUEST['id_responsable'])) {
		$sql.= " WHERE id_responsable=" . $_REQUEST['id_responsable'];
	}
	$sql.= " ORDER BY apenom";
	
	$rsResponsable = $mysqli->query($sql);
	while ($rowResponsable = $rsResponsable->fetch_object()) {
		?>
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="10"><?php echo "Responsable: " . $rowResponsable->apenom; ?></td></tr>
		<tr><td><?php echo "DNI: " . $rowResponsable->dni; ?></td><td><?php echo "Domicilio: " . $rowResponsable->domicilio; ?></td><td><?php echo "Localidad: " . $rowResponsable->localidad; ?></td></tr>
		<tr><td><?php echo "Telef.: " . $rowResponsable->telefono; ?></td><td><?php echo "Cargo: " . $rowResponsable->cargo; ?></td><td><?php echo "Organización: " . $rowResponsable->organizacion; ?></td></tr>
		
		
		<?php
		
		$sql = "SELECT";
		$sql.= " *";
		$sql.= " FROM vehiculo";
		$sql.= " WHERE id_responsable=" . $rowResponsable->id_responsable;
		$sql.= " ORDER BY nro_patente, nro_chasis, marca";
		
		$rsVehiculo = $mysqli->query($sql);
		if ($rsVehiculo->num_rows > 0) {
				?>
				
				<tr><td>&nbsp;</td></tr>
				<tr><th>Nro.patente</th><th>Nro.chasis</th><th>Marca</th></tr>
				
				<?php
			while ($rowVehiculo = $rsVehiculo->fetch_object()) {
				?>
				
				<tr><td><?php echo $rowVehiculo->nro_patente; ?></td><td><?php echo $rowVehiculo->nro_chasis; ?></td><td><?php echo $rowVehiculo->marca; ?></td></tr>
				
				<?php
			}
		}
		
		?>
		
		<tr><td>&nbsp;</td></tr>
		<tr><td colspan="10"><hr></td></tr>
		
		<?php
	}
		
	?>
	</table>
	</body>
	</html>
	<?php
	
break;
}

}

?>