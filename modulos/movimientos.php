<?

if ($_POST["accion"]=="guardar") {
	if($_POST["hk0967ih509"]==$_SESSION["authToken"]){
		// insertar el registro en tmovimientosinventario
		$idalmacen = $_POST["mialmacen"];
		$idalmacensecundario = $_POST["almacen"];

		$fecha = date("Y-m-d H:i:s");

		// recuperar el folio para asignarlo al movimiento
		$folio = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["foliomovimiento"] + 1;
		$folio2 = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idalmacen='".$idalmacensecundario."'"))["foliomovimiento"] + 1;
		if(mysqli_query($con,"insert into tmovimientosinventario (idusuario,tipousuario,idtipomovimiento,idalmacen,idalmacensecundario,idregistrosalida,tiporegistrosalida,folio,folio2,notas,fecha,status,autorizacion) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','V','3','".$idalmacen."','".$idalmacensecundario."','".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','V','".$folio."','".$folio2."','".$_POST["txtNotas"]."','".$fecha."','A','2')")){
			$movimientoinsertado = true;
			$idmovimientoinventario = mysqli_insert_id($con);
			// actualizar el folio
			mysqli_query($con,"update tsucursales set foliomovimiento = '".$folio."' where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')");
			mysqli_query($con,"update tsucursales set foliomovimiento = '".$folio2."' where idalmacen='".$idalmacensecundario."'");
			
			// insertar la lista de productos agregados en tmovimientoinventarioproductos
			mysqli_query($con,"insert into tmovimientoinventarioproductos (idmovimientoinventario,idproducto,idcolor,idtalla,idalmacen,idalmacensecundario,idtipomovimiento,cantidad) select '".$idmovimientoinventario."',idproducto,idcolor,idtalla,'".$idalmacen."','".$idalmacensecundario."','3',cantidad from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
			// al enviar el producto, las existencias del almacen que envia, se reducen. El almacen que recibe, no por el momento
			$partidas = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
			while($partida = mysqli_fetch_assoc($partidas)){
				mysqli_query($con,"update tproductoexistencias set existencia=existencia-".$partida["cantidad"]." where idalmacen='".$idalmacen."' and idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'");

				// registrar en tproductomovimientos
				$existencias = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'"))["existencia"];
				$existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."' and idalmacen='".$idalmacen."'"))["existencia"];
				mysqli_query($con,"insert into tproductomovimientos (idproducto,idusuario,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('".$partida["idproducto"]."','".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$partida["idtalla"]."','".$partida["idcolor"]."','".$idalmacen."','M','S','".$idmovimientoinventario."','".$partida["cantidad"]."','".$existencias."','".$existenciasalmacen."','".$fecha."')");

			}
	
			mysqli_query($con,"delete from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
		}else {
			$movimientoinsertado = false;
		}

	}
}

$where = "";

if ($_POST["slcProducto"]!=0) {
	$where .= " and idmovimientoinventario in (select idmovimientoinventario from vmovimientoproductos where idproducto='".$_POST["slcProducto"]."') ";
}
if ($_POST["slcTipoMovimiento"]!=0) {
	$where .= " and idtipomovimiento='".$_POST["slcTipoMovimiento"]."' ";
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

?>

<script>
<?
if (isset($movimientoinsertado)) {
	if ($movimientoinsertado) {
		?>
		Swal.fire("Movimiento realizado","El movimiento se ha realizado exitosamente","success");
		<?
	}else {
		?>
		Swal.fire("Error","Ocurrió un error al registrar el movimiento","error");
		<?
	}
}
?>

function toggleDetalle(idmovimiento){
	if($("#divMovimiento" + idmovimiento).is(":visible")){
		$("#divMovimiento" + idmovimiento).hide();
		$("#btnMovimiento" + idmovimiento).attr("css","fas fa-plus");
	}else{
		$("#divMovimiento" + idmovimiento).show();
		$("#btnMovimiento" + idmovimiento).attr("css","fas fa-minus");
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
	  <div class="navbar-text nav-title flex" id="pageTitle">Movimientos al inventario</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<form class="" action="" method="post" name="form" id="form">
				<div class="row">
					<div class=" col-sm-3">
						<select id="slcProducto" name="slcProducto" class="form-control" onChange="">
							<option value="0">Todos los productos</option>
							<?
							$productos = mysqli_query($con,"select * from tproductos order by nombre");
							while($producto = mysqli_fetch_assoc($productos)){
								?>
								<option value="<? echo $producto["idproducto"]; ?>" <? if($_POST["slcProducto"]==$producto["idproducto"]){?> selected <?} ?>><? echo $producto["nombre"];?></option>
								<?
							}
							?>
						</select>
					</div>
					<div class=" col-sm-3">
						<select id="slcTipoMovimiento" name="slcTipoMovimiento" class="form-control">
							<option value="0">Todos los movimientos</option>
							<option value="1" <? if($_POST["slcTipoMovimiento"]=="1"){?> selected <?} ?>>Entradas</option>
							<option value="2" <? if($_POST["slcTipoMovimiento"]=="2"){?> selected <?} ?>>Salidas</option>
						</select>
					</div>
					<div class=" col-sm-3">
						<button type="button" class="btn btn-primary btn-block waves-effect waves-light" name="btnFiltrar" id="btnFiltrar" onclick="submit();">Filtrar</button>
					</div>
					<div class="col-sm-3">
						<a href="?modulo1=movimientos&modulo2=agregar" class="btn btn-primary btn-block waves-effect pull-right" >Agregar</a>
					</div>
				</div>
			</form>
		</div>

		<div class="box-body">
			<div class="table-responsive">
			<?
			$mialmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];
			$movimientos = mysqli_query($con,"select * from vmovimientos where (idalmacen='".$mialmacen."' or idalmacensecundario='".$mialmacen."') ".$where." order by idmovimientoinventario desc");
			if (mysqli_num_rows($movimientos)>0) {
			?>
				<table class="table table-striped b-t">
					<thead>
						<tr>
							<th width="7%">#</th>
							<th width="7%"></th>
							<th>Tipo</th>
							<th>Almacen(es)</th>
							<th>Fecha</th>
							<th>Usuario</th>
							<th width="25"></th>
							<th width="25"></th>
							<th width="25"></th>
						</tr>
					</thead>
					<tbody>
					<?
					while ($movimiento = mysqli_fetch_assoc($movimientos)) {
						$folio = $movimiento["idalmacen"]==$mialmacen ? $movimiento["folio"] : $movimiento["folio2"];
						?>
						<tr id="<? echo $movimiento["idmovimientoinventario"]; ?>">
							<td><? echo $folio; ?></td>
							<td>
							<?
							if ($movimiento["idalmacensecundario"]==$mialmacen and $movimiento["autorizacion"]==0){
								?>
								<center><i class="fas fa-exclamation-triangle"></i></center>
								<?
							}else if ($movimiento["autorizacion"]==1){
								?>
								<center><i class="fas fa-ban"></i></center>
								<?
							}else if ($movimiento["autorizacion"]==2 and $movimiento["recepcionparcial"]==1){
								?>
								<center><i class="fas fa-asterisk"></i></center>
								<?
							}
							?>
							</td>
							<td><? echo ($movimiento["idtipomovimiento"]==1 ? "Entrada" : ($movimiento["idtipomovimiento"]==2 ? "Salida" : "Traspaso")); ?></td>
							<? $almacen = mysqli_fetch_assoc(mysqli_query($con,"select * from talmacenes where idalmacen='".$movimiento["idalmacen"]."'")); ?>
							<? $almacens = mysqli_fetch_assoc(mysqli_query($con,"select * from talmacenes where idalmacen='".$movimiento["idalmacensecundario"]."'")); ?>
							<td><? echo $almacen["nombre"] . ($almacens["nombre"]!="" ? "<br>" . $almacens["nombre"] : ""); ?></td>
							<td><? echo fecha_formateada($movimiento["fecha"]); ?></td>
							<td><? echo $movimiento["usuario"]; ?></td>
							<td>
								<a href="javascript:;" class="btn white" style="margin-right:15px;" onclick="toggleDetalle(<? echo $movimiento["idmovimientoinventario"]; ?>)"><i id="btnMovimiento<? echo $movimiento["idmovimientoinventario"]; ?>" class="fas fa-plus"></i></a> 
							</td>
							<td align="right">
								<a href="?modulo1=movimientos&modulo2=ver&idmovimientoinventario=<? echo $movimiento["idmovimientoinventario"]; ?>" class="btn white" style="margin-right:15px;"><i class="fas fa-info"></i></a> 
							</td>
							<td align="right">
								<a href="/modulos/movimientos/movimiento.php?idmovimientoinventario=<? echo $movimiento["idmovimientoinventario"]; ?>" class="btn white" target="_blank"><i class="fas fa-print"></i></a>
							</td>

						</tr>
						<tr id="divMovimiento<? echo $movimiento["idmovimientoinventario"]; ?>" style="display:none;">
							<td colspan="9">
								<?php
								$productos = mysqli_query($con,"select * from tmovimientoinventarioproductos where idmovimientoinventario='".$movimiento["idmovimientoinventario"]."'");
								?>
								<table class="table table-striped b-t">
									<thead>
										<tr>
											<th width="50">Cant</th>
											<th>Producto</th>
											<th width="100">Talla</th>
											<th width="100">Color</th>
										</tr>
									</thead>
									<tbody>
									<?
									while ($producto = mysqli_fetch_assoc($productos)) {
										$nombreproducto = ($producto["idproducto"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$producto["idproducto"]."'"))["nombre"] : $producto["producto"];
											
										$nombretalla = ($producto["idtalla"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$producto["idtalla"]."'"))["nombre"] : $producto["talla"];

										$nombrecolor = ($producto["idcolor"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$producto["idcolor"]."'"))["nombre"] : $producto["color"];
										
										$usuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario = '".$producto["idusuario"]."'"));
										?>
										<tr>
											<td><? echo $producto["cantidad"]; ?></td>
											<td><? echo $nombreproducto; ?></td>
											<td><? echo $nombretalla; ?></td>
											<td><? echo $nombrecolor; ?></td>
										</tr>
										<?
									}
									?>
									</tbody>
								</table>
								<?php
								if($movimiento["notas"]!=""){
									echo $movimiento["notas"]."<br>";
								}
								?>
							</td>
						</tr>
						<?
						}
					?>
					</tbody>
				</table>
			<?
			}else {
				?>
				<div class="box-body">
					<center><b><p>--No se encontraron movimientos al inventario--</p></b></center>
				</div>
				<?
			}
			?>
			</div>
		</div>
	</div>
</div>
