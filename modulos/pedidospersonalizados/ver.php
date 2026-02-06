<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

// $productospersonalizados = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and idpersonalizacion!=0");
if ($_POST["accion"]=="entregar") {
	foreach ($_POST["chkTicketProducto"] as $idcuentaproducto) {
		mysqli_query($con,"update trcuentaproductos set status='E' where idcuentaproducto='".$idcuentaproducto."'");
	}
}

?>

<script>
function entregarProductos() {
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
			text: "¿Estás seguro de que quieres entregar estos productos?",
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			cancelButtonText: "No",
			confirmButtonText: "Sí"
			}).then((result) => {
			if (result.value) {
				Swal.fire({
					title: 'Entrega',
					html: 'Productos entregados',
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
					$("#formPedidosPersonalizados").submit();
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
				}
			}
		});
	}else {
		$("input[name='chkTicketProducto[]']").each(function () {
			if(!$(this).is(":disabled")){
				if($(this).is(":checked")){
					$(this).prop("checked",false);
				}
			}
		});
	}
}

function calcularCambio(idpartida,chk) {
	if (chk.checked) {
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
	  <div class="navbar-text nav-title flex" id="pageTitle">Pedidos personalizados - Desglose</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
            <div class="p-2">
				<div class="row">
					<div class="col">
					<? 
					$pedido = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"));
					$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$pedido["idvendedor"]."'"));
					$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$pedido["idsucursal"]."' or idsucursal=3"));					
					echo "<b># Cuenta:</b> " . $pedido["idcuenta"] . "<br><b>Vendedor:</b> " . $vendedor["nombre"] . "<br><b>Sucursal:</b> " . $sucursal["nombre"] . "<br><b>Fecha:</b> " . fecha_formateada2($pedido["fecha"]) . "</b>"; 
					?>
					</div>
					<div class="col text-right" >
                        <a href="?modulo1=pedidos" class="btn btn-danger waves-effect pull-right">Regresar</a>
						<a href="javascript:;" onClick="entregarProductos();" class="btn btn-primary waves-effect waves-light">Entregar Productos</a>&nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </div>

		<div class="box-body">
            <div class="table-responsive">
			<? 
			// $partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and idpersonalizacion!=0");

			$partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and idcuenta in (select idcuenta from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from trcuentaproductopersonalizados)) order by idcuenta desc");
			if (mysqli_num_rows($partidas)>0) {
				?>
				<form name="formPedidosPersonalizados" id="formPedidosPersonalizados" method="post" action="">
					<input type="hidden" name="accion" id="accion" value="entregar">

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
										// echo "preciop: " . $preciopersonalizaciones;

										$precio = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."'"))["precio"] + $preciopersonalizaciones;
										$cantidad = $partida["cantidad"];
										$subtotalp = $precio*((100-$partida["descuento"])/100)*$cantidad/1.08;
										$ivap = $subtotalp*.08;
										$totalp = $subtotalp+$ivap;
										// mysqli_query($con,"update trticketproductostmp set subtotal='".$subtotalp."', iva='".$ivap."', total='".$totalp."' where idtmp='".$partida["idtmp"]."'");

										$producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
										$subtotal += (float)($partida["cantidad"]*($precio*((100-$partida["descuento"])/100)/1.08));
									?>
									<tr id="trpartida<? echo $partida["idcuentaproducto"]; ?>">
										<input type="hidden" name="precioPartida<? echo $partida["idcuentaproducto"]; ?>" id="precioPartida<? echo $partida["idcuentaproducto"]; ?>" value="<? echo $totalp; ?>">
										<td align="center"><input type="checkbox" name="chkTicketProducto[]" id="chkTicketProducto<? echo $partida["idcuentaproducto"]; ?>" value="<? echo $partida["idcuentaproducto"]; ?>" onClick="calcularCambio(<? echo $partida["idcuentaproducto"]; ?>,this)"></td>
										<td align="center"><? echo $partida["cantidad"]; ?></td>
										<?
										$personalizacion = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$partida["idpersonalizacion"]."'"));
										?>
										<td>
										<?
										echo $producto["nombre"] . ($partida["status"]=="E" ? " (ENTREGADO)" : "");
										// personalizaciones
										$personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
										while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
											$categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

											echo "<br> - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"];
										}
										?>
										</td>
										<!-- <td>
											<div class="row">
												<div class="col">
													<? echo $producto["nombre"]; ?>
												</div>
											</div>
										</td> -->
										<!-- <td>$<? echo number_format($producto["precio"]*((100-$partida["descuento"])/100),2); ?></td> -->
										<td>$<? echo number_format($precio,2); ?></td>
										
										<td>$<? echo number_format($precio*($partida["descuento"]/100),2) ?></td>
										<td>$<? echo number_format($totalp,2); ?></td>

										<script>
											<?
											if ($partida["status"]=="E") {
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
					<center><b><p>--No hay productos agregados--</p></b></center>
				</div>
				<?
			}
			?>
			</div>
		</div>
	</div>
</div>
