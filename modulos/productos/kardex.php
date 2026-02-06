<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

$producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$_GET["idproducto"]."'"));

$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal = '".$vendedor["idsucursal"]."'"));

$idtalla = $_POST["slcTalla"];
$idcolor = $_POST["slcColor"];

if($idtalla>0){
	$where .= " and idtalla = '".$idtalla."'";
}
if($idcolor>0){
	$where .= " and idcolor = '".$idcolor."'";
}

$movimientos = mysqli_query($con,"select * from vproductomovimientos where idproducto='".$_GET["idproducto"]."' and idalmacen = '".$sucursal["idalmacen"]."'".$where." order by idproductomovimiento desc");
?>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Kardex</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
		</div>
		<div class="box-body">

			<form name="formBusqueda" id="formBusqueda" action="" method="post">
			<input type="hidden" name="accion" value="busqueda">
			<div class="row mb-3 mt-0">
				<div class="col"><h4><? echo $producto["nombre"]; ?></h4></div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-md-3">
					<select name="slcTalla" id="slcTalla" class="form-control" onChange="formBusqueda.submit();">
						<option value="0">TODAS LAS TALLAS</option>
						<?php
						$tallas = mysqli_query($con,"select distinct(idtalla) as idtalla from tproductoexistencias where idproducto = '".$_GET["idproducto"]."'");
						while($talla = mysqli_fetch_assoc($tallas)){
							$t = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla = '".$talla["idtalla"]."'"));
							?>
							<option value="<? echo $t["idtalla"]; ?>" <? if($t["idtalla"]==$idtalla){ ?>selected<? } ?>><? echo $t["nombre"]; ?></option>
							<?
						}
						?>
					</select>
				</div>
				<div class="col-xs-12 col-md-3">
					<select name="slcColor" id="slcColor" class="form-control" onChange="formBusqueda.submit();">
						<option value="0">TODOS LOS COLORES</option>
						<?php
						$colores = mysqli_query($con,"select distinct(idcolor) as idcolor from tproductoexistencias where idproducto = '".$_GET["idproducto"]."'");
						while($color = mysqli_fetch_assoc($colores)){
							$c = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor = '".$color["idcolor"]."'"));
							?>
							<option value="<? echo $c["idcolor"]; ?>" <? if($c["idcolor"]==$idcolor){ ?>selected<? } ?>><? echo $c["nombre"]; ?></option>
							<?
						}
						?>
					</select>
				</div>
			</div>
			</form>
			<br>

			<div>

				<?
				if (mysqli_num_rows($movimientos)>0) {
				?>
				<table class="table table-striped b-t">
					<thead>
						<tr>
							<th>Fecha</th>
							<th>Usuario</th>
							<th>Tipo de Movimiento</th>
							<th>Folio</th>
							<th>Acción</th>
							<th width="80">Talla</th>
							<th width="80">Color</th>
							<th width="80">Cantidad</th>
							<th width="200">Existencias Almacen</th>
						</tr>
					</thead>
					<tbody>
					<?
					while ($movimiento = mysqli_fetch_assoc($movimientos)) {
						$folio = ($movimiento["idmovimiento"]>0) ? (($movimiento["origenmovimiento"]=="V" || $movimiento["origenmovimiento"]=="A") ? mysqli_fetch_assoc(mysqli_query($con,"select folio from ttickets where idcuenta = '".$movimiento["idmovimiento"]."' order by idticket limit 1"))["folio"] : $movimiento["idmovimiento"]) : "-";
						?>
						<tr>
							<td><? echo fecha_formateada($movimiento["fecha"]); ?></td>
							<td><? echo $movimiento["usuario"]; ?></td>
							<td><? echo (($movimiento["origenmovimiento"]=="P") ? "Producción" : (($movimiento["origenmovimiento"]=="M") ? "Movimiento" : (($movimiento["origenmovimiento"]=="C") ? "Compra" : (($movimiento["origenmovimiento"]=="V") ? "Venta" : (($movimiento["origenmovimiento"]=="A") ? "Apartado" : (($movimiento["origenmovimiento"]=="D") ? "Devolucion" : "")))))); ?></td>
							<td><? echo $folio; ?></td>
							<td><? echo (($movimiento["tipomovimiento"]=="E") ? "Entrada" : "Salida"); ?></td>
							<td><? echo $movimiento["talla"]; ?></td>
							<td><? echo $movimiento["color"]; ?></td>
							<td><? echo ($movimiento["tipomovimiento"]=="E") ? "+".$movimiento["cantidad"] : "<span style=\"color:red;\">-".$movimiento["cantidad"]."</span>"; ?></td>
							<td><? echo $movimiento["existenciasalmacen"]; ?></td>
						</tr>
						<?
						}
					?>
					</tbody>
				</table>
				<?
				}else{
				?>
				<br><br>
				No se encontraron movimientos para el color y talla seleccionada.
				<?
				}
				?>
			</div>
		</div>
	</div>
</div>