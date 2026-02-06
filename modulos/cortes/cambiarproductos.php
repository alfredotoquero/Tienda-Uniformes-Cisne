<?

// cambiar productos
if ($_POST["accion"]=="cambiar") {
	if($_POST["sd2581ak042"]==$_SESSION["authToken"]){
		
		$fecha = date("Y-m-d H:i:s");

		foreach ($_POST["partida"] as $i => $idpartida) {
			// al cambiar productos, se seleccionaron cantidades a cambiar que no necesariamente son iguales a la cantidad total del producto. El producto se descompone (a lo mucho) en tres partes:
			// -la cantidad a devolver (siempre mayor a cero)
			// -el restante (puede ser cero)
			// -el cambiado
			// el producto se vuelve a insertar dos veces en trcuentaproductos, el primero es el que se devuelve (tdevoluciones), y el segundo el que se cambia. De esta manera, el producto devuelto se puede ver en la pantalla de devoluciones etiquetado como "devuelto" y el cambiado como "no devuelto" (disponible para devolver)
			// ¿qué sucederá con el registro original en trcuentaproductos? ¿Sólo se le actualiza la cantidad?
			// NOTA: considerar las existencias de la devolucion y el cambio
			$partida = mysqli_fetch_assoc(mysqli_query($con,"select * from trcuentaproductos where idcuentaproducto='".$idpartida."'"));
			$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];

			if (mysqli_num_rows(mysqli_query($con,"select * from tdevoluciones where idcuentaproducto='".$partida["idcuentaproducto"]."' and idcuenta='".$partida["idcuenta"]."' and idproducto='".$partida["idproducto"]."'"))==0) {

				$idcorte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and status='A'"))["idcorte"];

				// al producto que se conserva (cantidad_original - cantidad_a_cambiar) se le actualiza su cantidad
				$restante = $partida["cantidad"] - $_POST["cantidad"][$i];
				if ($restante>0) {
					mysqli_query($con,"update trcuentaproductos set cantidad = '".$restante."' where idcuentaproducto='".$idpartida."'");

					// el producto que se devuelve se vuelve a insertar y se indica como devuelto
					mysqli_query($con,"insert into trcuentaproductos (idcuenta,idproducto,idcolor,idtalla,cantidad,subtotal,iva,total,status) values ('".$partida["idcuenta"]."','".$partida["idproducto"]."','".$partida["idcolor"]."','".$partida["idtalla"]."','".$_POST["cantidad"][$i]."','".$partida["subtotal"]."','".$partida["iva"]."','".$partida["total"]."','E')");

					// $idcuentaproducto = mysqli_insert_id($con);
					
				}else {
					// $idcuentaproducto = $partida["idcuentaproducto"];
				}
				
				mysqli_query($con,"insert into tdevoluciones (idcorte,idcuentaproducto,idcuenta,idproducto,fecha) values ('".$idcorte."','".$partida["idcuentaproducto"]."','".$partida["idcuenta"]."','".$partida["idproducto"]."','".$fecha."')");


				$idticket = mysqli_fetch_assoc(mysqli_query($con,"select idticket from ttickets where idcuenta = '".$partida["idcuenta"]."' order by idticket limit 1"))["idticket"];
	
				if (mysqli_num_rows(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."' and tipo!='S'"))>0) {
					// Es producto;
					mysqli_query($con,"update tproductoexistencias set existencia=existencia+".$_POST["cantidad"][$i]." where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."' and idalmacen='".$idalmacen."'");
	
					// agregar accion a tproductomovimientos
					$existencias = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'"))["existencia"];
					$existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."' and idalmacen='".$idalmacen."'"))["existencia"];
					mysqli_query($con,"insert into tproductomovimientos (idusuario,idproducto,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$partida["idproducto"]."','".$partida["idtalla"]."','".$partida["idcolor"]."','".$idalmacen."','D','E','".$idticket."','".$_POST["cantidad"][$i]."','".$existencias."','".$existenciasalmacen."','".$fecha."')");
					
				}else {
					// Es servicio;
				}


				// producto cambiado
				mysqli_query($con,"insert into trcuentaproductos (idcuenta,idproducto,idcolor,idtalla,cantidad,subtotal,iva,total,status) values ('".$partida["idcuenta"]."','".$partida["idproducto"]."','".$_POST["slcColor"][$i]."','".$_POST["slcTalla"][$i]."','".$_POST["cantidad"][$i]."','".$partida["subtotal"]."','".$partida["iva"]."','".$partida["total"]."','E')");

				$idticket = mysqli_fetch_assoc(mysqli_query($con,"select idticket from ttickets where idcuenta = '".$partida["idcuenta"]."' order by idticket limit 1"))["idticket"];
	
				if (mysqli_num_rows(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."' and tipo!='S'"))>0) {
					// Es producto;
					mysqli_query($con,"update tproductoexistencias set existencia=existencia-".$_POST["cantidad"][$i]." where idproducto='".$partida["idproducto"]."' and idcolor='".$_POST["slcColor"][$i]."' and idtalla='".$_POST["slcTalla"][$i]."' and idalmacen='".$idalmacen."'");
	
					// agregar accion a tproductomovimientos
					$existencias = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$_POST["slcColor"][$i]."' and idtalla='".$_POST["slcTalla"][$i]."'"))["existencia"];
					$existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$_POST["slcColor"][$i]."' and idtalla='".$_POST["slcTalla"][$i]."' and idalmacen='".$idalmacen."'"))["existencia"];
					mysqli_query($con,"insert into tproductomovimientos (idusuario,idproducto,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$partida["idproducto"]."','".$_POST["slcTalla"][$i]."','".$_POST["slcColor"][$i]."','".$idalmacen."','V','S','".$idticket."','".$_POST["cantidad"][$i]."','".$existencias."','".$existenciasalmacen."','".$fecha."')");
					
				}else {
					// Es servicio;
				}
			}
		}
		
	}
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>

<script>
$(document).ready(function (e) {
    
});

function formatMoney(n, c, d, t) {
  var c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
    j = (j = i.length) > 3 ? j % 3 : 0;

  return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

function cambiarProductos() {
	// al menos un producto
	var almenosuno = false;
	$("input[name='txtCantidad[]']").each(function () {
		if($(this).val()!=""){
            // $(this).val($(this).val() + "-" + $(this).attr("data-id"));
            // alert($(this).val());
            almenosuno = true;
		}
	});
	if (almenosuno) {
        // mandar a la pantalla de seleccion de colores y tallas
        $("#formCambiar").submit();
        // alert("entra");
        // for (var [key, value] of Object.entries(productos)) {
        //     console.log(key, value);
        // }
	} else {
		alert("ATENCION: Debes escribir una cantidad en al menos un producto.");
	}
	// return 0;
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
	  <div class="navbar-text nav-title flex" id="pageTitle">Cambio de Productos</div>
	</div>
</div>

<?
// $partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and status='E' and idcuentaproducto not in (select idcuentaproducto from trcuentaproductopersonalizados)");
$partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and idcuentaproducto not in (select idcuentaproducto from tdevoluciones)");
?>
<div class="padding">
	
	<div class="box" id="listaProductos">
		<div class="box-header">
			<div class="row">
				<div class="col">
					<? 
					$pedido = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"));
					$ticket = mysqli_fetch_assoc(mysqli_query($con,"select * from ttickets where idticket='".$_GET["idticket"]."'"));
					$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$pedido["idvendedor"]."'"));
					$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$pedido["idsucursal"]."'"));					
					echo "<b># Ticket:</b> " . $ticket["folio"] . "<br><b>Vendedor:</b> " . $vendedor["nombre"] . "<br><b>Sucursal:</b> " . $sucursal["nombre"] . "<br><b>Fecha:</b> " . fecha_formateada2($pedido["fecha"]) . "</b>"; 
					?>
				</div>

				<div class="col text-right">
					<?
					if (mysqli_num_rows($partidas)>0) {
						?>
						<a href="javascript:;" onClick="cambiarProductos();" class="btn btn-primary waves-effect waves-light">Cambiar Productos</a>&nbsp;&nbsp;
						<?
					}
					?>
					<a href="?modulo1=cortes&modulo2=detallev&idcorte=<? echo $_GET["idcorte"]; ?>#<? echo $_GET["idticket"]; ?>" class="btn btn-danger waves-effect waves-light">Regresar</a>
				</div>
			</div>
		</div>
	<?
		if (mysqli_num_rows($partidas)>0) {
			?>
			<form name="formCambiar" id="formCambiar" method="post" action="home.php?modulo1=cortes&modulo2=cambiarproductos&modulo3=seleccionarcambio&idcorte=<? echo $_GET["idcorte"]; ?>&idcuenta=<? echo $_GET["idcuenta"]; ?>&idticket=<? echo $_GET["idticket"]; ?>">
				<input type="hidden" name="accion" id="accion" value="">
				<input type="hidden" name="cambio" id="cambio" value="0">

				<div class="box-body" >
					<div class="table-responsive">
						<table class="table m-0">
							<thead>
								<tr>
									<th width="80"></th>
									<th width="30">Cant.</th>
									<th>Producto</th>
									<th width="100">Talla</th>
									<th width="180">Color</th>
									<th width="100">P.U.</th>
									<th width="100">Descuento</th>
									<th width="100">Total</th>
								</tr>
							</thead>
							<tbody>
								<?
								$subtotal = 0;
								while($partida = mysqli_fetch_assoc($partidas)){
									// calcular y guardar subtotal, iva y total de cada partida
									// se agrega costo adicional por cada personalizacion seleccionada
									$preciopersonalizaciones = mysqli_fetch_assoc(mysqli_query($con,"select sum(tc.precio) as precio from tcatpersonalizaciones tc, trcuentaproductopersonalizados tr where tc.idpersonalizacion in (select idpersonalizacion from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."') and tc.idpersonalizacion=tr.idpersonalizacion and tr.idcuentaproducto='".$partida["idcuentaproducto"]."'"))["precio"];
									// echo "preciop: " . $preciopersonalizaciones;

									$precio = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."'"))["precio"] + $preciopersonalizaciones;
									$cantidad = $partida["cantidad"];
									$subtotalp = $precio*((100-$partida["descuento"])/100)*$cantidad/1.08;
									$ivap = $subtotalp*.08;
									$totalp = $subtotalp+$ivap;

									$producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
									$color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor = '".$partida["idcolor"]."'"));
									$talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla = '".$partida["idtalla"]."'"));
									$subtotal += (float)($partida["cantidad"]*($precio*((100-$partida["descuento"])/100)/1.08));
								?>
								<tr id="trpartida<? echo $partida["idcuentaproducto"]; ?>">
									<input type="hidden" name="partida[]" id="partida" value="<? echo $partida["idcuentaproducto"]; ?>">
									<td align="center">
										<?
										if ($preciopersonalizaciones==0 and $partida["descuento"]==0) {
											?>
											<input type="text" name="txtCantidad[]" id="txtCantidad-<? echo $partida["idcuentaproducto"]; ?>" class="form-control" data-id="<? echo $partida["idcuentaproducto"]; ?>">
											<?
										}
										?>
									</td>
									<td align="center"><? echo $partida["cantidad"]; ?></td>
									<?
									?>
									<td>
									<?
									echo $producto["nombre"];
									// personalizaciones
									$personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
									while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
										$categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

										echo "<br> - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"];
									}
									?>
									</td>
									<td><? echo $talla["nombre"]; ?></td>
									<td><? echo $color["nombre"]; ?></td>
									<td>$<? echo number_format($precio,2); ?></td>
									
									<td>$<? echo number_format($precio*($partida["descuento"]/100),2) ?></td>
									<td>$<? echo number_format($totalp,2); ?></td>

								</tr>
								<?
								}
								$iva = ($subtotal) * 0.08;
								$total = (float)($subtotal) + (float)$iva;
								?>
							</tbody>
						</table>
					</div>
				</div>
			</form>
			<?
		}else {
			?>
			<div class="box-header">
			</div>
			<div class="box-body">
				<center><b><p>--No hay productos para cambiar--</p></b></center>
			</div>
			<?
		}
		?>
	</div>
</div>