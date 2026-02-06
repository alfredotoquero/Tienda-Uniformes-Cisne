<?
$where = "";

if (isset($_POST["txtNombreBusqueda"])) {
	$where .= " and nombre like '%".$_POST["txtNombreBusqueda"]."%' ";
	if ($_POST["slcTalla"]!=0) {
		$where .= " and idproducto in (select idproducto from tproductoexistencias where idtalla='".$_POST["slcTalla"]."') ";
	}
	if ($_POST["slcColor"]!=0) {
		$where .= " and idproducto in (select idproducto from tproductoexistencias where idcolor='".$_POST["slcColor"]."') ";
	}
	$where .= ($_POST["txtPrecioMin"]!="") ? " and precio>='".$_POST["txtPrecioMin"]."' " : "";
	$where .= ($_POST["txtPrecioMax"]!="") ? " and precio<='".$_POST["txtPrecioMax"]."' " : "";

	if ($_POST["slcExistencias"]!=0) {
		$where .= " and idproducto ".(($_POST["slcExistencias"]==2) ? "not": "")." in (select idproducto from tproductoexistencias where existencia>0 ".(($_POST["slcTalla"]!=0) ? " and idtalla='".$_POST["slcTalla"]."' " : "").(($_POST["slcColor"]!=0) ? " and idcolor='".$_POST["slcColor"]."' " : "").") ";
	}
}

$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal = '".$vendedor["idsucursal"]."'"));

// NO BORRAR
// $productos = mysqli_query($con,"select * from tproductos");
// while($producto = mysqli_fetch_assoc($productos)){
// 	$colores = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' group by idcolor");
// 	$tallas = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' group by idtalla");

// 	$almacenes = mysqli_query($con,"select * from talmacenes");

// 	echo "<br>";
// 	while($talla = mysqli_fetch_assoc($tallas)){
// 		while($color = mysqli_fetch_assoc($tallas)){
// 			while($almacen = mysqli_fetch_assoc($almacenes)){
// 				// si no tiene un registro para ese almacen, debe crearse uno
// 				if (mysqli_num_rows(mysqli_query($con,"select * from tproductoexistencias where idcolor='".$color["idcolor"]."' and idtalla='".$talla["idtalla"]."' and idproducto='".$producto["idproducto"]."' and idalmacen='".$almacen["idalmacen"]."'"))==0) {
// 					echo "idcolor: " . $color["idcolor"] . " idtalla: " . $talla["idtalla"] . " idproducto: " . $producto["idproducto"] . " idalmacen: " . $almacen["idalmacen"] . "<br>";
// 					mysqli_query($con,"insert into tproductoexistencias (idproducto,idcolor,idtalla,idalmacen,existencia) values ('".$producto["idproducto"]."','".$color["idcolor"]."','".$talla["idtalla"]."','".$almacen["idalmacen"]."',0)");
// 				} else {
// 					echo "";
// 				}
				
// 			}
// 		}	
// 	}
// }

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<script>
$(document).ready(function(e){
	$(".filtro").keypress(function(event){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == '13'){
			$("#btnFiltrar").click();     
		}
	}); 
});
</script>
<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Productos</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<div class="p-2">
				<!-- <div class="row mb-3">
				</div> -->
				<form class="" action="" method="post" name="form" id="form">
					<div class="row">
						<div class=" col-sm-3">
							<input type="text" class="form-control filtro" name="txtNombreBusqueda" id="txtNombreBusqueda" value="<? echo $_POST["txtNombreBusqueda"]; ?>" placeholder="Nombre del Producto">
						</div>
						<div class=" col-sm-3">
							<select id="slcTalla" name="slcTalla" class="form-control filtro" onChange="">
								<option value="0">Todas las tallas</option>
								<?
								$tallas = mysqli_query($con,"select * from tcattallas where nombre not regexp '[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-]' order by posicion");
								while($talla = mysqli_fetch_assoc($tallas)){
									?>
									<option value="<? echo $talla["idtalla"]; ?>" data-tipo="T" <? if($_POST["slcTalla"]==$talla["idtalla"]){?> selected <?} ?>><? echo $talla["nombre"];?></option>
									<?
								}
								$tallas = mysqli_query($con,"select * from tcattallas where nombre regexp '[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ]' and nombre not regexp '[-]' order by posicion");
								while($talla = mysqli_fetch_assoc($tallas)){
									?>
									<option value="<? echo $talla["idtalla"]; ?>" data-tipo="T" <? if($_POST["slcTalla"]==$talla["idtalla"]){?> selected <?} ?>><? echo $talla["nombre"];?></option>
									<?
								}
								$tallas = mysqli_query($con,"select * from tcattallas where nombre regexp '[-]' order by posicion");
								while($talla = mysqli_fetch_assoc($tallas)){
									?>
									<option value="<? echo $talla["idtalla"]; ?>" data-tipo="T" <? if($_POST["slcTalla"]==$talla["idtalla"]){?> selected <?} ?>><? echo $talla["nombre"];?></option>
									<?
								}
								?>
							</select>
						</div>
						<div class=" col-sm-3">
							<select id="slcColor" name="slcColor" class="form-control filtro" onChange="">
								<option value="0">Todos los colores</option>
								<?
								$colores = mysqli_query($con,"select * from tcatcolores order by nombre");
								while($color = mysqli_fetch_assoc($colores)){
									?>
									<option value="<? echo $color["idcolor"]; ?>" data-tipo="C" <? if($_POST["slcColor"]==$color["idcolor"]){?> selected <?} ?>><? echo $color["nombre"];?></option>
									<?
								}
								?>
							</select>
						</div>
						<div class=" col-sm-3">
							<input type="text" class="form-control filtro" name="txtPrecioMin" id="txtPrecioMin" value="<? echo $_POST["txtPrecioMin"]; ?>" placeholder="Precio Min">
						</div>
					</div>
					<div class="row" style="margin-top:15px;">
						<div class=" col-sm-3">
							<input type="text" class="form-control filtro" name="txtPrecioMax" id="txtPrecioMax" value="<? echo $_POST["txtPrecioMax"]; ?>" placeholder="Precio Max">
						</div>
						<div class=" col-sm-3">
							<select id="slcExistencias" name="slcExistencias" class="form-control filtro">
								<option value="0">Con o sin existencias</option>
								<option value="1" <? if($_POST["slcExistencias"]=="1"){?> selected <?} ?>>Con existencias</option>
								<option value="2" <? if($_POST["slcExistencias"]=="2"){?> selected <?} ?>>Sin existencias</option>
							</select>
						</div>
						<div class=" col-sm-3">
							<button type="button" class="btn btn-primary btn-block waves-effect waves-light" name="btnFiltrar" id="btnFiltrar" onclick="submit();">Filtrar</button>
						</div>
						<div class="col-xs-12 col-md-3">
							<a href="/modulos/productos/reporte.php" target="_blank" class="btn btn-primary btn-block">Reporte</a>
						</div>
					</div>
				</form>
			</div>


		</div>

		<div class="box-body">
			<div class="table-responsive">
			<?
			$productos = mysqli_query($con,"select * from tproductos where 1=1 and idproducto in (select idproducto from trproductoalmacenes where idalmacen in (select idalmacen from tsucursales where idsucursal='".$vendedor["idsucursal"]."'))" . $where . " order by nombre");
			if (mysqli_num_rows($productos)>0) {
			?>
				<table class="table table-striped b-t">
					<thead>
						<tr>
							<th>Nombre</th>
							<th>Precio</th>
							<th>Código de Barras</th>
							<th>Existencias</th>
							<th width="100"></th>
							<th width="100"></th>
							<th width="100"></th>
						</tr>
					</thead>
					<tbody>
					<?
					while ($producto = mysqli_fetch_assoc($productos)) {
						?>
						<tr>
							<td><? echo $producto["nombre"]; ?></td>
							<td>$<? echo number_format($producto["precio"],2); ?></td>
							<td><? echo $producto["codigobarras"]; ?></td>
							<td align="center">
							<? echo mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as cantidad from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen='".$sucursal["idalmacen"]."'"))["cantidad"]; ?>
							</td>
							<td>
								<a href="?modulo1=productos&modulo2=kardex&idproducto=<? echo $producto["idproducto"]; ?>" class="btn white" style="margin-right:15px;">Kardex</a>
							</td>
							<td align="right">
								<a href="javascript:;" onClick="fancy('/modulos/productos/precios.php?idproducto=<? echo $producto["idproducto"]; ?>',1000,300);" class="btn white" style="margin-right:15px;">Precios</a>
							</td>
							<td align="right">
								<?
								
								if (mysqli_num_rows(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idproducto in (select idproducto from tproductos where tipo!='S')"))>0) {
									?>
									<a href="javascript:;" onClick="fancy('/modulos/productos/existencias.php?idproducto=<? echo $producto["idproducto"]; ?>',1000,500);" class="btn white" style="margin-right:15px;">Existencias</a>
									<?
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
			}
			?>
			</div>
		</div>
	</div>
</div>
