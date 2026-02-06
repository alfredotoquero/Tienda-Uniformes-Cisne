<?
if ($_POST["accion"]=="recibir") {
	// se debe recorrer el arreglo de cantidades y añadir las existencias segun la cantidad especificada
	// aquellas cantidades que no hayan sido escritas o sean menor a la cantidad de la partida, se regresaran
	$recepcion = 2;
	$fecha = date("Y-m-d H:i:s");

	foreach ($_POST["txtCantidad"] as $i => $cantidad) {
		$cantidad = ($cantidad=="" ? 0 : $cantidad);
		$partida = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientoinventarioproductos where idmovimientoinventarioproducto='".$_POST["idmovimientoproducto"][$i]."'"));
		$cantdevolver = $partida["cantidad"] - $cantidad;

		$movimiento = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientosinventario where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'"));

		// si hay producto que se va a devolver, se debe guardar una notificacion
		if ($cantdevolver>0) {
			$idvendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idsucursal in (select idsucursal from tsucursales where idalmacen='".$partida["idalmacen"]."')"))["idvendedor"];
			// mysqli_query($con,"insert into tnotificacionesvendedores (idvendedor,notificacion) values ('".$idvendedor."','Se recibieron parcialmente los productos del movimiento')");
			// notificacion para el vendedor del almacen de salida
			mysqli_query($con,"insert into tnotificacionesvendedores (idvendedor,titulo,notificacion,tipo,idrelacionado) select idvendedor,'Movimiento Traspaso Recibido','<a href=\"?modulo1=movimientos\">Se han recibido parcialmente los productos del movimiento #".$movimiento["folio"]." </a>','2','".$_GET["idmovimientoinventario"]."' from tvendedores where idsucursal in (select idsucursal from tsucursales where idalmacen='".$movimiento["idalmacen"]."')");
			// recepcion=1 significa que se recibio parcialmente
			$recepcion = 1;
		}else {
			// notificacion para el vendedor del almacen de salida
			mysqli_query($con,"insert into tnotificacionesvendedores (idvendedor,titulo,notificacion,tipo,idrelacionado) select idvendedor,'Movimiento Traspaso Recibido','<a href=\"?modulo1=movimientos\">Se han recibido totalmente los productos del movimiento #".$movimiento["folio"]." </a>','2','".$_GET["idmovimientoinventario"]."' from tvendedores where idsucursal in (select idsucursal from tsucursales where idalmacen='".$movimiento["idalmacen"]."')");
		}

		mysqli_query($con,"update tproductoexistencias set existencia=existencia+".$cantidad." where idalmacen='".$partida["idalmacensecundario"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'");
		mysqli_query($con,"update tproductoexistencias set existencia=existencia+".$cantdevolver." where idalmacen='".$partida["idalmacen"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'");

		
		mysqli_query($con,"update tmovimientoinventarioproductos set cantidadrecibida='".$cantidad."' where idmovimientoinventarioproducto='".$partida["idmovimientoinventarioproducto"]."'");

		// tproductomovimientos
		$existencias = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'"))["existencia"];
		$existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."' and idalmacen='".$partida["idalmacensecundario"]."'"))["existencia"];

		mysqli_query($con,"insert into tproductomovimientos (idproducto,idusuario,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('".$partida["idproducto"]."','".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$partida["idtalla"]."','".$partida["idcolor"]."','".$partida["idalmacensecundario"]."','M','E','".$_GET["idmovimientoinventario"]."','".$cantidad."','".$existencias."','".$existenciasalmacen."','".$fecha."')");
	}

	mysqli_query($con,"update tmovimientosinventario set idregistroentrada='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."',tiporegistroentrada='V' where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");

	mysqli_query($con,"update tmovimientosinventario set recepcionparcial='".$recepcion."' where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");
}

if ($_POST["accion"]=="cancelar") {
	// se devuelden las existencias de los productos de este movimiento
	$partidas = mysqli_query($con,"select * from tmovimientoinventarioproductos where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");
	while($partida = mysqli_fetch_assoc($partidas)){
		mysqli_query($con,"update tproductoexistencias set existencia=existencia+".$partida["cantidad"]." where idalmacen='".$partida["idalmacen"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'");
	}

	mysqli_query($con,"update tmovimientosinventario set autorizacion=1 where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");

	$movimiento = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientosinventario where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'"));

	mysqli_query($con,"insert into tnotificacionesvendedores (idvendedor,titulo,notificacion,tipo,idrelacionado) select idvendedor,'Movimiento Traspaso Cancelado','<a href=\"?modulo1=movimientos\">Se ha cancelado la recepción de los productos del movimiento #".$movimiento["folio"]." </a>','2','".$_GET["idmovimientoinventario"]."' from tvendedores where idsucursal in (select idsucursal from tsucursales where idalmacen='".$movimiento["idalmacen"]."')");
}

// si se autoriza, debe hacerse la salida de los productos del almacen correspondiente
if ($_POST["accion"]=="autorizar") {

	$movimiento = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientosinventario where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'"));
	// registrar al vendedor que dio salida a los productos del movimiento
	mysqli_query($con,"update tmovimientosinventario set idregistrosalida='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."',tiporegistrosalida='V' where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");

	// conseguir todos los productos del movimiento y actualizar las existencias (como es una salida, se restan)
	$partidas = mysqli_query($con,"select * from tmovimientoinventarioproductos where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");
	while($partida = mysqli_fetch_assoc($partidas)){
		mysqli_query($con,"update tproductoexistencias set existencia=existencia-".$partida["cantidad"]." where idalmacen='".$partida["idalmacen"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'");
	}

	mysqli_query($con,"update tmovimientosinventario set autorizacion=2 where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");

	// notificacion para el vendedor del almacen de entrada
	mysqli_query($con,"insert into tnotificacionesvendedores (idvendedor,titulo,notificacion,tipo,idrelacionado) select idvendedor,'Movimiento Traspaso Autorizado','<a href=\"?modulo1=movimientos\">Se ha autorizado la recepción de productos del movimiento #".$movimiento["folio2"]." </a>','2','".$_GET["idmovimientoinventario"]."' from tvendedores where idsucursal in (select idsucursal from tsucursales where idalmacen='".$movimiento["idalmacensecundario"]."')");
}

if ($_POST["accion"]=="noautorizar") {
	// cuando no se autoriza, ¿se registra un usuario (por ejemplo, para saber quien lo canceló)?
	mysqli_query($con,"update tmovimientosinventario set idregistrosalida='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."',tiporegistrosalida='V' where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");

	mysqli_query($con,"update tmovimientosinventario set autorizacion=1 where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");
}

$movimiento = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientosinventario where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'"));

$mialmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>

<script>
function recibirProductos() {
	// validar que al menos un input tenga una cantidad escrita
	var almenosuno = false;
	$("input[name*='txtCantidad[]']").each(function (e) {
		if (Number($(this).val())>0) {
			almenosuno = true;
		}
	});
	// calcular si se esta recibiendo menos producto de al menos una partida
	var menosproducto = false;
	// validar que ninguna cantidad escrita exceda la de la partida respectiva
	var cantidadesvalidas = true;
	var idmovimientoinventario = 0;
	$("input[name*='cantidadProducto[]']").each(function (e) {
		idmovimientoinventario = $(this).prop("id").split("-")[1];
		if (Number($("#txtCantidad-" + idmovimientoinventario).val())>Number($(this).val())) {
			cantidadesvalidas = false;
		} 
		if (Number($("#txtCantidad-" + idmovimientoinventario).val())<Number($(this).val())) {
			menosproducto = true;
		} 
	});

	if (!almenosuno) {
		alert("ATENCION: Debes ingresar al menos una cantidad");
	} else if (!cantidadesvalidas){
		alert("ATENCION: Alguna cantidad no es valida o se excede de la cantidad a recibir");
	} else if (menosproducto) {
		if (confirm("ATENCION: Se esta recibiendo menos producto. ¿Deseas continuar?")){
			$("#accion").val("recibir");
			$("#formRecepcion").submit();
		}
	} else{
		$("#accion").val("recibir");
		$("#formRecepcion").submit();
	}
}

function cancelarProductos() {
	if (confirm("ATENCION: Estás seguro de que deseas cancelar los productos?")) {
		$("#accion").val("cancelar");
		$("#formRecepcion").submit();
	}
}

function realizarAutorizacion(accion) {
	$("#accion").val(accion);
	if (accion=="autorizar") {
		if (confirm("ATENCION: Estás seguro de que deseas autorizar?")) {
			$("#formRecepcion").submit();
		}
	}else{
		if (confirm("ATENCION: Estás seguro de que deseas no autorizar?")) {
			$("#formRecepcion").submit();
		}
	}
}

function validate(evt) {
    var theEvent = evt || window.event;

    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
    // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    // var regex = /[0-9]|\./;
    var regex = /[0-9]/;
    if( !regex.test(key) ) {
        theEvent.returnValue = false;
        if(theEvent.preventDefault) theEvent.preventDefault();
    }

}
</script>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen --> 
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Detalle del movimiento</div>
	</div>
</div>


<div class="padding">
	<div class="box">
		<div class="box-header">
			<div class="p-2">
				<div class="row">
					<div class="col-sm-12">
						<a href="?modulo1=movimientos#<? echo $movimiento["idmovimientoinventario"]; ?>" class="btn btn-danger waves-effect pull-right" style="float:right;margin-right:0px;">Regresar</a>
						<? 
						echo "Tipo de movimiento: <b>" . ($movimiento["idtipomovimiento"]=="1" ? "Entrada" : ($movimiento["idtipomovimiento"]=="2" ? "Salida" : "Traspaso")) . "</b>";
						$almacen = mysqli_fetch_assoc(mysqli_query($con,"select * from talmacenes where idalmacen='".$movimiento["idalmacen"]."'")); 
						$almacens = mysqli_fetch_assoc(mysqli_query($con,"select * from talmacenes where idalmacen='".$movimiento["idalmacensecundario"]."'"));
						echo "<br># de movimiento: <b>" . $movimiento["folio"] . "</b>"; 
						if ($movimiento["tipousuario"]=="V") {
							$nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$movimiento["idusuario"]."'"))["nombre"]; 
						}else {
							$nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario='".$movimiento["idusuario"]."'"))["nombre"];
						}
                        echo "<br>Usuario: <b>" . $nombre. "</b>"; 
                        echo "<br>Almacen" . ($almacens["nombre"]!="" ? " origen" : "") . ": <b>" . $almacen["nombre"]. "</b>";
						if ($movimiento["idregistrosalida"]>0) {
							if ($movimiento["tiporegistrosalida"]=="V") {
								$nombreusuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$movimiento["idregistrosalida"]."'"))["nombre"];
							}else{
								$nombreusuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario='".$movimiento["idregistrosalida"]."'"))["nombre"];
							}
							echo "<br>Usuario origen: <b>" . $nombreusuario . "</b>";
						}
						echo $movimiento["idalmacensecundario"]>0 ? "<br>Almacen destino: <b>" . $almacens["nombre"]. "</b>" : "";
						if ($movimiento["idregistroentrada"]>0) {
							if ($movimiento["tiporegistroentrada"]=="V") {
								$nombreusuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$movimiento["idregistroentrada"]."'"))["nombre"];
							}else{
								$nombreusuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario='".$movimiento["idregistroentrada"]."'"))["nombre"];
							}
							echo "<br>Usuario destino: <b>" . $nombreusuario . "</b>";
						}

						echo "<br>Fecha: <b>" . fecha_formateada($movimiento["fecha"]). "</b>"; 
						echo ("<br>Estado: <b>" . ($movimiento["autorizacion"]==0 ? "Sin autorizar" : ($movimiento["autorizacion"]==1 ? "Cancelado" : ($movimiento["recepcionparcial"]==0 ? "Autorizado" : "Recibido"))) . "</b>"); 
						echo ($movimiento["notas"]!="") ? "<br>Notas: ".$movimiento["notas"] : "";
						?>
					</div>
				</div>
				<div class="row">
					<div class="col text-right">
						<?
						// si el almacen destino es el almacen del vendedor en cuestion, recibe o cancela productos
						if ($mialmacen==$movimiento["idalmacensecundario"] and $movimiento["autorizacion"]==2 and $movimiento["recepcionparcial"]==0) {
							?>
							<a href="javascript:;" onClick="recibirProductos();" class="btn btn-primary waves-effect waves-light">Recibir Productos</a>&nbsp;&nbsp;
							<a href="javascript:;" onClick="cancelarProductos();" class="btn btn-danger waves-effect waves-light">Cancelar Productos</a>&nbsp;&nbsp;
							<?
						}
						// si el almacen origen es el almacen del vendedor y aún no ha sido autorizado, debe aparecer la opcion para que lo pueda autorizar o no autorizar
						if ($mialmacen==$movimiento["idalmacen"] and $movimiento["autorizacion"]==0) {
							?>
							<a href="javascript:;" onClick="realizarAutorizacion('autorizar');" class="btn btn-primary waves-effect waves-light">Autorizar</a>&nbsp;&nbsp;
							<a href="javascript:;" onClick="realizarAutorizacion('noautorizar');" class="btn btn-danger waves-effect waves-light">No Autorizar</a>
							<?
						}
						?>
					</div>
				</div>
				<div class="row">
					<div class="col text-right">
						<a href="/modulos/movimientos/movimiento.php?idmovimientoinventario=<? echo $_GET["idmovimientoinventario"]; ?>" target="_blank" class="btn btn-primary waves-effect waves-light mt-3">Imprimir</a>
					</div>
				</div>
			</div>
		</div>

		<div class="box-body">
			<div class="table-responsive">
			<?
			$productos = mysqli_query($con,"select * from tmovimientoinventarioproductos where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'");
			if (mysqli_num_rows($productos)>0) {
			?>
				<form name="formRecepcion" id="formRecepcion" method="post" action="">
					<input type="hidden" name="accion" id="accion" value="recibir">
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<?
								if ($mialmacen==$movimiento["idalmacensecundario"] and $movimiento["autorizacion"]==2 and $movimiento["recepcionparcial"]==0) {
									?>
									<th width="75"></th>
									<?
								}
								?>
								<th width="50">Cantidad</th>
								<?
								if ($movimiento["autorizacion"]==2) {
									?>
									<th width="120">Cantidad Recibida</th>
									<?
								}
								?>
								<th>Producto</th>
								<th width="100">Talla</th>
								<th width="100">Color</th>
							</tr>
						</thead>
						<tbody>
						<?
						while ($producto = mysqli_fetch_assoc($productos)) {
							$nombreproducto = ($producto["idproducto"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$producto["idproducto"]."'"))["nombre"] : $producto["producto"];
								
							$nombretalla = ($producto["idtalla"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$producto["idtalla"]."'"))["nombre"] : ($producto["talla"]=="" ? "-" : $producto["talla"]);

							$nombrecolor = ($producto["idcolor"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$producto["idcolor"]."'"))["nombre"] : ($producto["color"]=="" ? "-" : $producto["color"]);
							
							$usuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario = '".$producto["idusuario"]."'"));
							?>
							<tr>
								<?
								if ($mialmacen==$movimiento["idalmacensecundario"] and $movimiento["autorizacion"]==2 and $movimiento["recepcionparcial"]==0) {
									?>
									<td>
										<input type="hidden" name="idmovimientoproducto[]" id="" value="<? echo $producto["idmovimientoinventarioproducto"]; ?>">
										<input type="text" name="txtCantidad[]" id="txtCantidad-<? echo $producto["idmovimientoinventarioproducto"]; ?>" class="form-control" onKeypress="validate(event);">
									</td>
									<?
								}
								?>
								<td><? echo $producto["cantidad"]; ?></td>
								<?
								if ($movimiento["autorizacion"]==2) {
									?>
									<td><? echo $producto["cantidadrecibida"]; ?></td>
									<?
								}
								?>
								<input type="hidden" name="cantidadProducto[]" id="cantidadProducto-<? echo $producto["idmovimientoinventarioproducto"] ?>" value="<? echo $producto["cantidad"]; ?>">
								<td><? echo $nombreproducto; ?></td>
								<td><? echo $nombretalla; ?></td>
								<td><? echo $nombrecolor; ?></td>
							</tr>
							<?
							}
						?>
						</tbody>
					</table>
				</form>
			<?
			}
			?>
			</div>
		</div>
	</div>
</div>
