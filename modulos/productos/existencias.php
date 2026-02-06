<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- for ios 7 style, multi-resolution icon of 152x152 -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
    <link rel="apple-touch-icon" href="/assets/images/logo.svg">
    <meta name="apple-mobile-web-app-title" content="Flatkit">
    <!-- for Chrome on Android, multi-resolution icon of 196x196 -->
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="shortcut icon" sizes="196x196" href="/assets/images/logo.svg">

    <!-- style -->
    <link rel="stylesheet" href="/libs/font-awesome/css/font-awesome.min.css" type="text/css" />

    <!-- build:css /assets/css/app.min.css -->
    <link rel="stylesheet" href="/libs/bootstrap/dist/css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="/assets/css/app.css" type="text/css" />
    <link rel="stylesheet" href="/assets/css/style.css" type="text/css" />
    <link href="/assets/plugins/select2/dist/css/select2.css" rel="stylesheet" type="text/css">
    <link href="/assets/plugins/select2/dist/css/select2-bootstrap.css" rel="stylesheet" type="text/css">

    <script src="/assets/plugins/moment/moment.js"></script>


    <!-- Datepicker and Wickedpicker CSS -->
    <link href="/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <link href="/libs/wickedpicker/dist/wickedpicker.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="/libs/jquery/dist/jquery.min.js"></script>
    <!-- <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script> -->


    <!-- Datepicker and Wickedpicker JS -->
    <script src="/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="/libs/wickedpicker/dist/wickedpicker.min.js"></script>
    <script src="/assets/plugins/select2/dist/js/select2.min.js" type="text/javascript"></script>

    <!--  -->
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
    <script src="/assets/js/sweetalert2.min.js"></script>

    <!-- Optional: include a polyfill for ES6 Promises for IE11 and Android browser -->
    <script src="/assets/js/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill"></script>
    <!--  -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-lite.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-lite.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css" />
    <script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>

</head>
<body>

<script>
function fancy(url,w,h){
    parent.$.fancybox.close();
    parent.fancy(url,w,h);
}
</script>

<?
// 
$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$almacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$vendedor["idsucursal"]."'"));
$producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$_GET["idproducto"]."'"));
?>

<div class="padding">
	<div class="box">

		<div class="box-body">
			<div class="table-responsive">
			<table class="table m-0">
				<thead>
					<tr>
						<th width="30">Producto</th>
						<?
						// sucursales
                        $sucursales = mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$vendedor["idvendedor"]."') limit 1");
						while($sucursal = mysqli_fetch_assoc($sucursales)){
							?>
							<th align="center"><? echo $sucursal["nombre"]; ?></th>
							<?
                        }
                        $sucursales = mysqli_query($con,"select * from tsucursales where idsucursal not in (select idsucursal from tvendedores where idvendedor='".$vendedor["idvendedor"]."')");
						while($sucursal = mysqli_fetch_assoc($sucursales)){
							?>
							<th align="center"><? echo $sucursal["nombre"]; ?></th>
							<?
                        }
                        $almacenes = mysqli_query($con,"select * from talmacenes where idalmacen not in (select idalmacen from tsucursales where status = 'A')");
						while($almacen = mysqli_fetch_assoc($almacenes)){
                            ?>
                            <th align="center"><? echo $almacen["nombre"]; ?></th>
                            <?
                        }
                        ?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><? echo $producto["nombre"]; ?></td>
						<?
						// existencias por sucursal
						// para sacar las existencias por sucursal, se debe conseguir el almacen y sumar las existencias donde el idproducto (ignorando color y talla) sea igual al recibido
                        // mysqli_data_seek($sucursales,0);
						$sucursales = mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$vendedor["idvendedor"]."') limit 1");
						while($sucursal = mysqli_fetch_assoc($sucursales)){
							$cantidad = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as cantidad from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen='".$sucursal["idalmacen"]."'"))["cantidad"];
							?>
							<td align="center">
                            <?
                            if ($cantidad>0) {
                                // si es un producto sin colores ni tallas, no debe tener enlace al desglose
                                if (mysqli_num_rows(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen = '".$sucursal["idalmacen"]."'"))<1) {
                                    echo $cantidad;
                                } else {
                                    ?>
                                    <a href="javascript:;" onClick="fancy('/modulos/productos/desgloseexistencias.php?idproducto=<? echo $producto["idproducto"]; ?>&idalmacen=<? echo $sucursal["idalmacen"]; ?>',1000,500);" style="margin-right:15px;"><? echo $cantidad; ?></a></td>
                                    <?
                                }
                                
                                ?>
                                <?
                            } else {
                                echo 0; 
                            }
                        }
                        $sucursales = mysqli_query($con,"select * from tsucursales where idsucursal not in (select idsucursal from tvendedores where idvendedor='".$vendedor["idvendedor"]."')");
						while($sucursal = mysqli_fetch_assoc($sucursales)){
							$cantidad = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as cantidad from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen='".$sucursal["idalmacen"]."'"))["cantidad"];
							?>
							<td align="center">
                            <?
                            if ($cantidad>0) {
                                // si es un producto sin colores ni tallas, no debe tener enlace al desglose
                                if (mysqli_num_rows(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen = '".$sucursal["idalmacen"]."'"))<1) {
                                    echo $cantidad;
                                } else {
                                    ?>
                                    <a href="javascript:;" onClick="fancy('/modulos/productos/desgloseexistencias.php?idproducto=<? echo $producto["idproducto"]; ?>&idalmacen=<? echo $sucursal["idalmacen"]; ?>',1000,500);" style="margin-right:15px;"><? echo $cantidad; ?></a></td>
                                    <?
                                }
                                
                                ?>
                                <?
                            } else {
                                echo 0; 
                            }
                        }
                        $almacenes = mysqli_query($con,"select * from talmacenes where idalmacen not in (select idalmacen from tsucursales where status = 'A')");
						while($almacen = mysqli_fetch_assoc($almacenes)){
                            $cantidad = mysqli_fetch_assoc(mysqli_query($con,"select sum(existencia) as cantidad from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen='".$almacen["idalmacen"]."'"))["cantidad"];
                            ?>
                            <td align="center">
                            <?
                            if ($cantidad>0) {
                                // si es un producto sin colores ni tallas, no debe tener enlace al desglose
                                if (mysqli_num_rows(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen = '".$almacen["idalmacen"]."'"))<1) {
                                    echo $cantidad;
                                } else {
                                    ?>
                                    <a href="javascript:;" onClick="fancy('/modulos/productos/desgloseexistencias.php?idproducto=<? echo $producto["idproducto"]; ?>&idalmacen=<? echo $almacen["idalmacen"]; ?>',1000,500);" style="margin-right:15px;"><? echo $cantidad; ?></a></td>
                                    <?
                                }
                                
                                ?>
                                <?
                            } else {
                                echo 0; 
                            }
                        }
						?>
					</tr>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>

</body>
</html>