<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// si tengo al menos un producto, deben bloquearse: idtipomovimiento y almacenes

if ($_POST["accion"]=="agregar") {
	// $error = true;
	$error = false;
	// cambiar este metodo, debe agregar tantos registros como inputs con una cantidad escrita haya
	
	// $colores = mysqli_query($con,"select * from tcatcolores where idcolor in (select idcolor from tproductoexistencias where idproducto='".$_POST["idproducto"]."')");
	// $tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$_POST["idproducto"]."')");
	$colores = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' group by idcolor");
	$tallas = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' group by idtalla");

	while ($color = mysqli_fetch_assoc($colores) ) {
		while ($talla = mysqli_fetch_assoc($tallas)) {
			if ($_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]>0) {
				// insertar
				mysqli_query($con,"insert into tmovimientoinventarioproductostmp (idusuario,idpartida,idproducto,idtalla,idcolor,cantidad) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$_POST["idpartida"]."','".$_POST["idproducto"]."','".$talla["idtalla"]."','".$color["idcolor"]."','".$_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]."')");
				// echo $_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]] . "<br>";
			}
		}
		mysqli_data_seek($tallas,0);
	}

    // $idtmp = mysqli_insert_id($con);

    // if ($idtmp>0) {
    //     $error = false;
	// }
	
	// aumentar idmovimientopartida
	?>
	<script>
	$("#idmovimientopartida").val(Number($("#idmovimientopartida").val()) + 1);
	</script>
	<?
}

if ($_POST["accion"]=="eliminar") {
	// cambiar este metodo, debe eliminar todos los registros que pertenezcan al mismo idtmp
	mysqli_query($con,"delete from tmovimientoinventarioproductostmp where idpartida='".$_POST["idpartida"]."' and idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
}

if ($_POST["accion"]=="editar") {
	// cambiar este metodo, debe borrar y volver a introducir los registros
	mysqli_query($con,"delete from tmovimientoinventarioproductostmp where idpartida='".$_POST["idpartida"]."' and idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
	
	// if ($_POST["idproducto"]!="") {
		$colores = mysqli_query($con,"select * from tcatcolores where idcolor in (select idcolor from tproductoexistencias where idproducto='".$_POST["idproducto"]."')");
		$tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$_POST["idproducto"]."')");
		// if (mysqli_num_rows($tallas)>0) {
			while ($color = mysqli_fetch_assoc($colores) ) {
				while ($talla = mysqli_fetch_assoc($tallas)) {
					if ($_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]>0) {
						// insertar
						mysqli_query($con,"insert into tmovimientoinventarioproductostmp (idusuario,idpartida,idproducto,idtalla,idcolor,cantidad) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$_POST["idpartida"]."','".$_POST["idproducto"]."','".$talla["idtalla"]."','".$color["idcolor"]."','".$_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]."')");
						// echo $_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]] . "<br>";
					}
				}
				mysqli_data_seek($tallas,0);
			}
		// }
		
	// }
}

if ($_POST["accion"]=="cargarinfo") {
		// NOTA: este método carga la informacion de la partida seleccionada (algunos datos son fijos), cambia el boton de agregar por el de editar, y carga la tabla
		$producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idpartida='".$_POST["idpartida"]."'"));
		?>
		<script>
			$("#idproducto").val(<? echo $producto["idproducto"]; ?>);
			$("#idpartida").val(<? echo $producto["idpartida"]; ?>);
			$("#slcProducto").val("<? echo $producto['idproducto']; ?>");
			$("#slcProducto").prop('disabled', true);
			$("#slcProducto").select2("val", $("#slcProducto").val());
	
			// ocultar boton agregar y mostrar boton editar
			$("#divAgregar").hide();
			$("#divEditar").show();
		</script>
		<?	
}

$partidas = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' group by idpartida order by idpartida"); 
?>

<?
if (mysqli_num_rows($partidas)>0){
	?>
	<script>
		$("#slcTipoMovimiento").prop("disabled",true);
		$("#slcAlmacen").prop("disabled",true);
	</script>

<div class="box">
	<div class="box-header">
		<p style="margin-top: 30px;">Lista de productos agregados.</p>
	</div>
	<div class="box-body">
		<table class="table m-0">
			<thead>
				<tr>
					<th>Producto</th>
					<th width="10"></th>
					<th width="10"></th>
				</tr>
			</thead>
			<tbody>
				<?
				while($partida = mysqli_fetch_assoc($partidas)){
					$producto = mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'");
					// si el producto es libre, entonces el nombre, talla y color no vienen de la tabla, sino de lo que se insertó ($partida["producto"], $partida["talla"] y $partida["color"])
					?>
				<tr>
					<td>
						<? 
						// if (mysqli_num_rows($producto)>0) {
							// no tiene colores
							// se debe desplegar solo la cantidad
							$cantidad = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idpartida='".$partida["idpartida"]."' and idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idcolor=0 and idtalla=0"))["cantidad"];
							echo $cantidad . " ";
							$nombre = mysqli_fetch_assoc($producto)["nombre"];
							echo $nombre . "<br>";

							$dcolores = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idpartida='".$partida["idpartida"]."' and idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' group by idcolor having idcolor!=0");
							// if (mysqli_num_rows($dcolores)>0) {
								while ($dcolor = mysqli_fetch_assoc($dcolores)) {
									$color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$dcolor["idcolor"]."'"))["nombre"];
									echo $color;
									$dtallas = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idpartida='".$partida["idpartida"]."' and idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idcolor='".$dcolor["idcolor"]."'");
									while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
										// talla
										$talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
										echo " / " . $dtalla["cantidad"] . " - " . $talla;
									}
									?>
									<br>
									<? 
								}
							// }else {
							// }
							
	
						// }
						
			
						?>
					</td>
					<td align="right"><a href="javascript:;" onClick="cargarDatos(<? echo $partida["idpartida"]; ?>,<? echo $partida["idproducto"]; ?>);"><i class="fas fa-pencil-alt"></i></a></td>
					<td align="right"><a href="javascript:;" onClick="eliminarProducto(<? echo $partida["idpartida"]; ?>);"><i class="fa fa-times"></i></a></td>

				</tr>
				<?
				}
				?>
			</tbody>
		</table>
	</div>
</div>
<?
}else {
	?>
	<script>
	$("#slcAlmacen").prop("disabled",false);
	$("#slcTipoMovimiento").prop("disabled",false);
	</script>
	<center><p><b>--No hay productos agregados--</b></p></center>
	<?
}
?>
<script>
<?
if($_POST["accion"]=="agregar" && $error){
?>
alert("ERROR: No se pudo agregar el producto a la cotización.");
<?
}
?>
</script>
