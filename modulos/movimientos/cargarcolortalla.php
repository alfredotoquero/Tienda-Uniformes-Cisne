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

    <script>
    function agregar(){
        var total = 0;
        $(".cantidades").each(function () {
            total += Number($(this).val());
        });
        if (total==0) {
            alert("ATENCION: debes introducir al menos una cantidad");
        }else{
            // validar que las cantidades introducidas no son mayores a las existencias correspondientes
            $.ajax({
                type: "POST",
                url: "/modulos/movimientos/validarexistenciasalmacen.php",
                data: $("#formColorTalla").serialize(),
                async: false,
                success: function (data){
                    if (data=="OK") {
                        $.ajax({
                            type:"POST",
                            url:"/modulos/movimientos/listaProductos.php",
                            // agregar el form completo para enviar todos los inputs de la tabla
                            data: $("#formColorTalla").serialize(),
                            success: function(data){
                                // mostrar las tallas y colores del producto seleccionado
                                parent.$("#listaProductos").html(data);
                                parent.limpiarInputs();
                                parent.$.fancybox.close();
                            }
                        });
                    }else{
                        var datos = data.split("|");
                        // alert("Alguna cantidad excede la respectiva existencia");
                        if (datos[2]=="") {
                            alert("No hay existencias suficientes");
                        }else{
                            alert("El producto con color " + datos[1] + " y talla " + datos[2] + " no posee suficientes existencias");

                        }
                    }
                    // alert(data);
                }
            });
        }
    }

    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

  </script>

  </head>
<body>

<div class="container-fluid" style="padding-left: 20px; padding-right: 20px;">

    <div class="box-header">
        <center><b>Ingresa los datos solicitados</b> </center>
        <!-- <small>Los campos marcados con * son obligatorios</small> -->
    </div>

	<form name="formColorTalla" id="formColorTalla" action="" method="post">
        <input type="hidden" name="idproducto" id="idproducto" value="<? echo $_GET["idproducto"]; ?>">
        <input type="hidden" name="almacen" id="almacen" value="<? echo $_GET["idalmacen"]; ?>">
        <input type="hidden" name="accion" id="accion" value="<? echo $_GET["accion"]; ?>">
        <input type="hidden" name="idpartida" id="idpartida" value="<? echo $_GET["idpartida"]; ?>">
        <input type="hidden" name="idtipomovimiento" id="idtipomovimiento" value="3">

        <?
        if (mysqli_num_rows(mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$_GET["idproducto"]."' group by idtalla)"))>0) {
            ?>
            <table class="table m-0">
                <thead>
                    <tr>
                        <th width="30">Color\Talla</th>
                        <?
                        // tallas
                        $tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$_GET["idproducto"]."' group by idtalla) order by posicion");
                        while ($talla = mysqli_fetch_assoc($tallas)) {
                            ?>
                            <th><? echo $talla["nombre"]; ?></th>
                            <?
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?
                    // colores
                    $colores = mysqli_query($con,"select * from tcatcolores where idcolor in (select idcolor from tproductoexistencias where idproducto='".$_GET["idproducto"]."' group by idcolor)");
                        while($color = mysqli_fetch_assoc($colores)){
                        ?>
                        <tr>
                            <td><? echo $color["nombre"]; ?></td>
                            <?
                            mysqli_data_seek($tallas,0);
                            while ($talla = mysqli_fetch_assoc($tallas)) {
                                $desglose = mysqli_fetch_assoc(mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idpartida='".$_GET["idpartida"]."' and idtalla='".$talla["idtalla"]."' and idcolor='".$color["idcolor"]."'"));
                                ?>
                                <td>
                                    <input type="text" name="txtCantidad<? echo $color["idcolor"] . "-" . $talla["idtalla"]; ?>" id="txtCantidad<? echo $color["idcolor"] . "-" . $talla["idtalla"]; ?>" class="form-control cantidades" value="<? echo $desglose["cantidad"]>0 ? $desglose["cantidad"] : ""; ?>" onkeypress="return isNumber(event);">
                                </td>
                                <? 
                            }
                            ?>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
            <?
        }else {
            ?>
            <div class="row">
                <div class="col-2">
                    Cantidad
                </div>
                <div class="col-10">
                    <input type="text" name="txtCantidad0-0" id="txtCantidad0-0" class="form-control cantidades">
                </div>
            </div>
            <?
        }
        ?>
        <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="col-5"></div>
			<div class="col-4">
                <button type="button" onClick="agregar();" class="btn btn-primary btn-sm">SELECCIONAR</button>
            </div>
		</div>
	</form>
</div>

</body>
</html>
