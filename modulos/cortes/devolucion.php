<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

// devolver productos
if ($_POST["accion"]=="devolver") {
	foreach ($_POST["chkTicketProducto"] as $idcuentaproducto) {
		$partida = mysqli_fetch_assoc(mysqli_query($con,"select * from trcuentaproductos where idcuentaproducto='".$idcuentaproducto."'"));
		$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];
		
		if (mysqli_num_rows(mysqli_query($con,"select * from tdevoluciones where idcuentaproducto='".$partida["idcuentaproducto"]."' and idcuenta='".$partida["idcuenta"]."' and idproducto='".$partida["idproducto"]."'"))==0) {

			// $idcorte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idcorte in (select idcorte from tcuentas where idcuenta = '".$partida["idcuenta"]."')"))["idcorte"];
			// $idcorte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and status='A'"))["idcorte"];
			$idcorte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idsucursal = '".$vendedor["idsucursal"]."' and status='A'"))["idcorte"];

			mysqli_query($con,"insert into tdevoluciones (idcorte,idcuentaproducto,idcuenta,idproducto,fecha) values ('".$idcorte."','".$partida["idcuentaproducto"]."','".$partida["idcuenta"]."','".$partida["idproducto"]."','".date("Y-m-d H:i:s")."')");

			$idticket = mysqli_fetch_assoc(mysqli_query($con,"select idticket from ttickets where idcuenta = '".$partida["idcuenta"]."' order by idticket limit 1"))["idticket"];

			if (mysqli_num_rows(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."' and tipo!='S'"))>0) {
				// echo "Es producto";
				mysqli_query($con,"update tproductoexistencias set existencia=existencia+".$partida["cantidad"]." where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."' and idalmacen='".$idalmacen."'");

				// agregar accion a tproductomovimientos
				$existencias = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."'"))["existencia"];
				$existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as existencia from tproductoexistencias where idproducto='".$partida["idproducto"]."' and idcolor='".$partida["idcolor"]."' and idtalla='".$partida["idtalla"]."' and idalmacen='".$idalmacen."'"))["existencia"];
				mysqli_query($con,"insert into tproductomovimientos (idusuario,idproducto,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$partida["idproducto"]."','".$partida["idtalla"]."','".$partida["idcolor"]."','".$idalmacen."','D','E','".$idticket."','".$partida["cantidad"]."','".$existencias."','".$existenciasalmacen."','".date("Y-m-d H:i:s")."')");

			}else {
				// echo "Es servicio";
			}
		}
	}
}
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

function devolverProductos() {
	// al menos un producto
	var almenosuno = false;
	$("input[name='chkTicketProducto[]']").each(function () {
		if(!$(this).is(":disabled")){
			if($(this).is(":checked")){
				almenosuno = true;
			}
		}
	});
	if (almenosuno) {
		Swal.fire({
			title: "ATENCION: ",
			text: "¿Estás seguro de que quieres devovler estos productos?",
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			cancelButtonText: "No",
			confirmButtonText: "Sí"
			}).then((result) => {
			if (result.value) {
				Swal.fire({
					title: 'Devolución',
					html: '\
					MXN: <label for="">$' + formatMoney($("#cambio").val()) + '</label><br>\
					USD: <label for="">$' + formatMoney($("#cambio").val()/18) + '</label>\
					',
					type: "success",
					inputAttributes: {
						autocapitalize: 'off'
					},
					showCancelButton: false,
					confirmButtonText: 'Aceptar',
					closeOnConfirm: false,
					allowOutsideClick: false
				}).then((result) => {
					// $("#accion").val("cobrar");
					$("#formDevolucion").submit();
				});
			}
		});
	} else {
		alert("ATENCION: Debes seleccionar al menos un producto.");
	}
	// return 0;
}

function seleccionarTodos() {
	if ($("#chkTodos").is(":checked")) {
		$("input[name='chkTicketProducto[]']").each(function () {
			if(!$(this).is(":disabled")){
				if(!$(this).is(":checked")){
					$(this).prop("checked",true);
					$("#cambio").val(Number($("#cambio").val()) + Number($("#precioPartida"+$(this).val()).val()));
				}
			}
		});
	}else {
		$("input[name='chkTicketProducto[]']").each(function () {
			if(!$(this).is(":disabled")){
				if($(this).is(":checked")){
					$(this).prop("checked",false);
					$("#cambio").val(Number($("#cambio").val()) - Number($("#precioPartida"+$(this).val()).val()));
				}
			}
		});
	}
}

function calcularCambio(idpartida,chk) {
	if (chk.checked) {
		$("#cambio").val(Number($("#cambio").val()) + Number($("#precioPartida"+idpartida).val()));
		var todos = true;
		$("input[name='chkTicketProducto[]']").each(function () {
			if(!$(this).is(":disabled")){
				if(!$(this).is(":checked")){
					todos = false;
				}
			}
		});
		if(todos){
			$("#chkTodos").prop("checked",true);
		}
	}else{
		$("#cambio").val(Number($("#cambio").val()) - Number($("#precioPartida"+idpartida).val()));
		$("#chkTodos").prop("checked",false);
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
	  <div class="navbar-text nav-title flex" id="pageTitle">Devolución</div>
	</div>
</div>

<?
$partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and status='E' and idcuentaproducto not in (select idcuentaproducto from trcuentaproductopersonalizados)");
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
						<a href="javascript:;" onClick="devolverProductos();" class="btn btn-primary waves-effect waves-light">Devolver Productos</a>&nbsp;&nbsp;
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
			<form name="formDevolucion" id="formDevolucion" method="post" action="home.php?modulo1=cortes&modulo2=devolucion&idcorte=<? echo $_GET["idcorte"]; ?>&idcuenta=<? echo $_GET["idcuenta"]; ?>">
				<input type="hidden" name="accion" id="accion" value="devolver">
				<input type="hidden" name="cambio" id="cambio" value="0">

				<div class="box-body" >
					<div class="table-responsive">
						<table class="table m-0">
							<thead>
								<tr>
									<th width="30"><input type="checkbox" name="chkTodos" id="chkTodos" value="1" onClick="seleccionarTodos()"></th>
									<th width="30">Cant.</th>
									<th>Producto</th>
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

									$precio = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."'"))["precio"] + $preciopersonalizaciones;
									$cantidad = $partida["cantidad"];
									$subtotalp = $precio*((100-$partida["descuento"])/100)*$cantidad/1.08;
									$ivap = $subtotalp*.08;
									$totalp = $subtotalp+$ivap;

									$producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
									$subtotal += (float)($partida["cantidad"]*($precio*((100-$partida["descuento"])/100)/1.08));
								?>
								<tr id="trpartida<? echo $partida["idcuentaproducto"]; ?>">
									<input type="hidden" name="precioPartida<? echo $partida["idcuentaproducto"]; ?>" id="precioPartida<? echo $partida["idcuentaproducto"]; ?>" value="<? echo $totalp; ?>">
									<td align="center"><input type="checkbox" name="chkTicketProducto[]" id="chkTicketProducto<? echo $partida["idcuentaproducto"]; ?>" value="<? echo $partida["idcuentaproducto"]; ?>" onClick="calcularCambio(<? echo $partida["idcuentaproducto"]; ?>,this)"></td>
									<td align="center"><? echo $partida["cantidad"]; ?></td>
									<?
									?>
									<td>
									<?
									echo $producto["nombre"] . (mysqli_num_rows(mysqli_query($con,"select * from tdevoluciones where idcuentaproducto='".$partida["idcuentaproducto"]."'"))>0 ? " (DEVUELTO)" : "");
									// personalizaciones
									$personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
									while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
										$categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

										echo "<br> - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"];
									}
									?>
									</td>
									<td>$<? echo number_format($precio,2); ?></td>
									
									<td>$<? echo number_format($precio*($partida["descuento"]/100),2) ?></td>
									<td>$<? echo number_format($totalp,2); ?></td>

									<script>
										<?
										if (mysqli_num_rows(mysqli_query($con,"select * from tdevoluciones where idcuentaproducto='".$partida["idcuentaproducto"]."'"))>0) {
											?>
											$("#chkTicketProducto<? echo $partida["idcuentaproducto"]; ?>").prop("checked",true);
											$("#chkTicketProducto<? echo $partida["idcuentaproducto"]; ?>").prop("disabled",true);
											$("#trpartida<? echo $partida["idcuentaproducto"]; ?>").css("background-color","#F6F6F6");
											<?
										}
										?>
									</script>
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
				<center><b><p>--No hay productos para devolución--</p></b></center>
			</div>
			<?
		}
		?>
	</div>
</div>