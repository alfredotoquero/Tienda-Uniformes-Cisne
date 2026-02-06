<?
// borrar la informacion de la tabla demporal de este usuario
mysqli_query($con,"delete from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");

// el almacen que maneja el vendedor. Se necesita para poder validar que al menos un select tiene el almacen del vendedor
$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>
<script>
$(document).ready(function (e) {
	$("#slcProducto").select2();
});

function validarForm() {
	// debe haber al menos un producto agregado
	$.ajax({
		type: "POST",
		url: "/modulos/movimientos/validaralmenosunproducto.php",
		data: "idalmacen=" + $("#mialmacen").val(),
		success: function (data){
			if (data=="OK") {
				$("#accion").val("guardar");
				$("#formMovimiento").submit();
			}else if (data=="ERROR1") {
				alert("Debes tener al menos un producto agregado");
			}else if (data=="ERROR2") {
				alert("Alguna cantidad excede la respectiva existencia");
			}
		}
	});
}

function agregarProducto() {
	// NOTA: cuando hay al menos un producto agregado, las opciones superiores (almacen y almacen secundario) deben quedar fijos
	// NOTA: cuando se hace una salida o un traspaso, debe haber suficiente producto en el almacen (en el caso del traspaso, el almacen origen) para que se pueda agregar
	var total = 0;
	$(".cantidades").each(function () {
		total += Number($(this).val());
	});
		
	
	if ($("#slcAlmacen").val()==0) {
    	alert("ATENCION: debes seleccionar un almacen");
    	$("#slcAlmacen").focus();
	}else if ($("#slcProducto").val()==0) {
    	alert("ATENCION: debes seleccionar un producto");
    	$("#slcProducto").focus();
	}else{
		fancy('/modulos/movimientos/cargarcolortalla.php?idproducto=' + $("#idproducto").val() + '&idalmacen=' + $("#mialmacen").val() + '&accion=agregar&idpartida=' + $("#idmovimientopartida").val(),1100,600);
	}
}

function cargarColorTallaProducto(idproducto) { 
	$("#idproducto").val(idproducto);
}

function cargarDatos(idpartida,idproducto) {
	fancy('/modulos/movimientos/cargarcolortalla.php?idproducto=' + idproducto + '&idalmacen=' + $("#almacen").val() + '&accion=editar&idpartida=' + idpartida,1100,600);
}

function eliminarProducto(idpartida) {
	// elimina producto
	// si se elimina el producto que se habia seleccionado para editar, se deben reiniciar los datos superiores
	if ($("#idpartida").val()==idpartida) {
		$("#slcProducto").prop('disabled', false);
		$("#divAgregar").show();
		$("#divEditar").hide();
	}
	$.ajax({
		type:"POST",
		url:"/modulos/movimientos/listaProductos.php",
		data: "idpartida=" + idpartida + "&accion=eliminar",
		success: function(data){
			$("#listaProductos").html(data);
		}
	});
}

function cargarAlmacenes(idalmacen) {
	$("#almacen").val(idalmacen);
}

function limpiarInputs() {
	$("#divColorTalla").html("");
	$("#slcProducto").val(0);
	$("#idproducto").val("");
	$("#slcProducto").select2("val", 0);
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
		<div class="navbar-text nav-title flex" id="pageTitle">Agregar Movimiento</div>
	</div>
</div>

<div class="padding">
	<div class="row">
		<div class="col-xs-12 col-md-12">
			<div class="box">
				<div class="box-header">
					<div class="row">
						<div class="col-md-10">
							<b>Ingresa los datos solicitados</b> <small>Los campos marcados con * son obligatorios</small>
						</div>
						<div class="col-md-2">
							<button type="button" class="btn btn-danger btn-block waves-effect waves-light" onClick="location.href = '?modulo1=movimientos';">Cancelar</button>
						</div>
					</div>
				</div>
				<div class="box-divider m-0"></div>
				<div class="box-body">
					<form id="formMovimiento" name="formMovimiento" class="form-horizontal" role="form" method="post" autocomplete="off" enctype="multipart/form-data" action="?modulo1=movimientos">
						<input type="text" name="hidden" autocomplete="nope" value="" style="display:none;">
						<input type="hidden" name="accion" id="accion" value="">
						<input type="hidden" name="idmovimientopartida" id="idmovimientopartida" value="1">
						<input type="hidden" name="mialmacen" id="mialmacen" value="<? echo $idalmacen; ?>">
						<input type="hidden" name="hk0967ih509" value="<? echo $_SESSION["authToken"]; ?>">

						<div class="form-group">
							<div class="row">
								<div class="col-2" ><label for="">Almacen</label><span>*</span></div>
								<div class="col-10">
									<input type="hidden" name="almacen" id="almacen" value="">
									<select name="slcAlmacen" id="slcAlmacen" class="form-control" onchange="cargarAlmacenes(this.value);">
										<option value="0">--Selecciona un almacen--</option>
										<?
										$almacenes = mysqli_query($con,"select * from talmacenes where idalmacen!='".$idalmacen."'");
										while($almacen = mysqli_fetch_assoc($almacenes)){
											?>
											<option value="<? echo $almacen["idalmacen"]; ?>"><? echo $almacen["nombre"]; ?></option>
											<?
										}
										?>
									</select>
								</div>
							</div>
						</div>

                        <div class="form-group">
                            <div class="row">
								<div class="col-2">
                                </div>
                                <div class="col-5" id="selectProducto">
                                    <label for="">Producto</label>
                                    <input type="hidden" name="idpartida" id="idpartida">
                                    <input type="hidden" name="idproducto" id="idproducto">
                                    <select name="slcProducto" id="slcProducto" class="form-control" onchange="cargarColorTallaProducto(this.value);">
                                        <option value="0">--Selecciona un Producto--</option>
                                        <?
                                        $productos = mysqli_query($con,"select * from tproductos where idproducto in (select idproducto from tproductoexistencias) and tipo!='S'");
                                        while ($producto = mysqli_fetch_assoc($productos)) {
                                            ?>
                                            <option value="<? echo $producto["idproducto"]; ?>"><? echo $producto["nombre"]; ?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-2" id="divAgregar">
                                    <label for="">&nbsp;</label>
                                    <button type="button" class="btn btn-primary btn-block waves-effect waves-light" onclick="agregarProducto();">Agregar</button>
                                </div>
                            </div>
                        </div>

						<div id="divColorTalla"></div>

                        <div id="listaProductos"></div>

						<div class="form-group">
							<div class="row">
								<div class="col-2" ><label for="">Notas</label></div>
								<div class="col-10">
									<textarea name="txtNotas" id="txtNotas" rows="5" class="form-control"></textarea>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="row">
								<div class="col-md-offset-8 col-md-2">
									<button type="button" class="btn btn-primary btn-block waves-effect waves-light" onClick="validarForm();">Guardar</button>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>