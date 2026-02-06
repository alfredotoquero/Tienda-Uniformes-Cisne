<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

include("../../assets/php/classes/Pedidos.php");
include("../../assets/php/classes/Clientes.php");

$clasePedidos = new Pedidos($con);
$claseClientes = new Clientes($con);

function fecha_formateada($fecha){
	$fecha = explode(" ",$fecha);
	$hora = $fecha[1];
	$fecha = $fecha[0];
	
	$fecha = explode("-",$fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia."/";

	switch($mes){
		case "01": $fecha.= "Ene"; break;
		case "02": $fecha.= "Feb"; break;
		case "03": $fecha.= "Mar"; break;
		case "04": $fecha.= "Abr"; break;
		case "05": $fecha.= "May"; break;
		case "06": $fecha.= "Jun"; break;
		case "07": $fecha.= "Jul"; break;
		case "08": $fecha.= "Ago"; break;
		case "09": $fecha.= "Sep"; break;
		case "10": $fecha.= "Oct"; break;
		case "11": $fecha.= "Nov"; break;
		case "12": $fecha.= "Dic"; break;
	}

	$fecha .= "/".$ano;
	
	if($hora!=""){
		$fecha .= "<br>".date("h:i a",strtotime($hora));
	}
	
	return $fecha;
}

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
$pedido = $clasePedidos->infoPedido($_GET["idpedido"]);
if($pedido["respuesta"]=="OK"){
?>
<div class="container" style="padding-left: 0px; padding-right: 0px;padding-top:25px;">

    <div class="box">
        <div class="box-header text-center"><b>Información del Pedido #<? echo $pedido["idpedido"]; ?></b></div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12 col-md-12"><b>Cliente:</b> <? echo $pedido["cliente"]; ?></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">Correo: <? echo $pedido["correocliente"]; ?></div>
                <div class="col-xs-12 col-md-6">Telefono: <? echo $pedido["telefonocliente"]; ?></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-12"><b>Contacto:</b> <? echo $pedido["contacto"]; ?></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">Correo: <? echo $pedido["correocontacto"]; ?></div>
                <div class="col-xs-12 col-md-6">Telefono: <? echo $pedido["telefonocontacto"]; ?></div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-4"><b>Total:</b> $<? echo number_format($pedido["total"],2); ?></div>
                <div class="col-xs-12 col-md-4"><b>Abonado:</b> $<? echo number_format($pedido["abonado"],2); ?></div>
                <div class="col-xs-12 col-md-4"><b>Restante:</b> $<? echo number_format($pedido["total"]-$pedido["abonado"],2); ?></div>
            </div>
        </div>
    </div>

    <? if($pedido["motivofinalizacion"]!=""){ ?>
    <div class="box">
        <div class="box-header text-center"><b>Motivo de finalización</b></div>
        <div class="box-body"><? echo $pedido["motivofinalizacion"]; ?></div>
    </div>
    <? } ?>

    <div class="box">
        <div class="box-header text-center"><b>Historial de Pagos del Pedido #<? echo $pedido["idpedido"]; ?></b></div>

        <div class="box-body">
            <?
            $pagos = mysqli_query($con,"select * from tticketspedidos where idpedido='".$_GET["idpedido"]."' ");
            if(mysqli_num_rows($pagos)>0){
            ?>
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>Monto</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    while($pago = mysqli_fetch_assoc($pagos)){
                        if($pago["idusuario"]>0){
                            $nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario='".$pago["idusuario"]."'"))["nombre"];
                        }else{
                            $nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$pago["idvendedor"]."'"))["nombre"];
                        }
                        ?>
                        <tr>
                            <td><? echo "$" . number_format($pago["total"],2); ?></td>
                            <td><? echo $nombre; ?></td>
                            <td><? echo fecha_formateada($pago["fecha"]); ?></td>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>
            <? 
            }else{
            ?> 
            No se encontraron pagos registrados
            <?
            }
            ?>
        </div>
    </div>

    <div class="box">
        <div class="box-header text-center"><b>Productos del Pedido #<? echo $pedido["idpedido"]; ?></b></div>

        <div class="box-body">
            <?
            $partidas = mysqli_query($con,"select * from trcotizacionproductos where idpedido='".$pedido["idpedido"]."' order by idcotizacionproducto");
            if(mysqli_num_rows($partidas)>0){
            ?>
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>P.U.</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    while($partida = mysqli_fetch_assoc($partidas)){
                        $nombreproducto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$partida["idproducto"]."'"))["nombre"];
                        if ($nombreproducto=="") {
                            $nombreproducto = $partida["producto"];
                        }
                        $nombrecolor = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$partida["idcolor"]."'"))["nombre"];
                        $nombretalla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$partida["idtalla"]."'"))["nombre"];
                        ?>
                        <tr>
                            <td>
                                <?php
                                echo $nombreproducto;

                                $cadenaespecificaciones = "";
                                $cadenaespecificaciones .= (($partida["serigrafia1"]>0 || $partida["serigrafia2"]>0 || $partida["serigrafia3"]>0) ? "Serigrafía ".$partida["serigrafia1"]." / ".$partida["serigrafia2"]." / ".$partida["serigrafia3"] : "");
                                $cadenaespecificaciones .= ($partida["personalizadonumero"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."Personalizado Número" : "");
                                $cadenaespecificaciones .= ($partida["personalizadonombre"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."Personalizado Nombre" : "");
                                $cadenaespecificaciones .= ($partida["bordado1"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."1 Bordado" : "");
                                $cadenaespecificaciones .= ($partida["bordado2"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."2 Bordados" : "");
                                $cadenaespecificaciones .= ($partida["bordado3"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."3 Bordados" : "");
                                $cadenaespecificaciones .= ($partida["bordado4"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."4 Bordados" : "");
                                $cadenaespecificaciones .= ($partida["bordadoespecial"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."Bordado Especial" : "");
                                $cadenaespecificaciones .= ($partida["personalizado1linea"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."Personalizado 1 Linea" : "");
                                $cadenaespecificaciones .= ($partida["personalizado2lineas"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."Personalizado 2 Lineas" : "");
                                $cadenaespecificaciones .= ($partida["personalizado3lineas"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."Personalizado 3 Lineas" : "");
                                $cadenaespecificaciones .= ($partida["sxl"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."S-XL" : "");
                                $cadenaespecificaciones .= ($partida["2xl"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."2XL" : "");
                                $cadenaespecificaciones .= ($partida["3xl"]==1 ? (($cadenaespecificaciones!="") ? ", " : "")."3XL" : "");
                                $cadenaespecificaciones .= ($partida["observaciones"]!="" ? (($cadenaespecificaciones!="") ? ", " : "")."Observaciones: " . $partida["observaciones"] : "");
                                echo ($cadenaespecificaciones!="") ? "<br><br>".$cadenaespecificaciones."<br>" : "<br><br>";

                                $dcolores = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$pedido["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idproducto!=0 group by idcolor,idcotizacionproducto having idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                                while($dcolor = mysqli_fetch_assoc($dcolores)){
                                
                                    echo "<br>".mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$dcolor["idcolor"]."'"))["nombre"];
                                
                                    $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$pedido["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$dcolor["idcolor"]."' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                                    while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                                        // talla
                                        $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                                        echo " / " . $dtalla["cantidad"] . " - " . $talla;
                                    }

                                }

                                $desgloses = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$pedido["idpedido"]."' and idproducto=0 and producto='".$partida["producto"]."' group by color,idcotizacionproducto having color!='' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                                while ($desglose = mysqli_fetch_assoc($desgloses)) {
                                    
                                    echo "<br>".$desglose["color"];

                                    $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$pedido["idpedido"]."' and idproducto=0 and color='".$desglose["color"]."' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                                    while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                                        // talla
                                        $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                                        echo " / " . $dtalla["cantidad"] . " - " . $talla;
                                    }
                                }
                                ?>
                            </td>
                            <td><? echo $partida["cantidad"]; ?></td>
                            <td><? echo "$" . number_format($partida["precio"],2); ?></td>
                            <td><? echo "$" . number_format(($partida["precio"]*$partida["cantidad"]),2); ?></td>
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

    <div class="box">
        <div class="box-header text-center"><b>Producción del Pedido #<? echo $pedido["idpedido"]; ?></b></div>
        <div class="box-body">
        <?
        $iconos = array("stop","play","check");
        $especificaciones = $clasePedidos->obtenerEspecificacionesPedido($pedido["idpedido"]);
        if($especificaciones["respuesta"]=="OK"){
            foreach($especificaciones["especificaciones"] as $especificacion) {
                //status diseño
                if($especificacion["statusdiseno"]>0){
                    if($especificacion["statusdiseno"]<4){
                        $iconodiseno = $iconos[1];
                        $statusdiseno = "warning";
                    }else{
                        $iconodiseno = $iconos[2];
                        $statusdiseno = "success";
                    }
                }else{
                    $iconodiseno = $iconos[0];
                    $statusdiseno = "danger";
                }

                //status produccion
                if($especificacion["statusproduccion"]>0){
                    if($especificacion["statusproduccion"]<2){
                        $iconoproduccion = $iconos[1];
                        $statusproduccion = "warning";
                    }else{
                        $iconoproduccion = $iconos[2];
                        $statusproduccion = "success";
                    }
                }else{
                    $iconoproduccion = $iconos[0];
                    $statusproduccion = "danger";
                }

                //status almacen
                if($especificacion["cantidadsurtida"]>0){
                    if($especificacion["cantidadrequerida"]>$especificacion["cantidadsurtida"]){
                        $iconoalmacen = $iconos[1];
                        $statusalmacen = "warning";
                    }else{
                        $iconoalmacen = $iconos[2];
                        $statusalmacen = "success";
                    }
                }else{
                    $iconoalmacen = $iconos[0];
                    $statusalmacen = "danger";
                }
                ?>
                <div class="row p-2" style="border-top: 1px solid rgba(222,222,222,0.5);">
                    <div class="col-xs-12 col-md-12">
                        <div class="row mb-2">
                            <div class="col-6 text-left">
                                Fecha de Entrega: <? echo fecha_formateada($especificacion["fechaentrega"]); ?>
                            </div>
                            <div class="col-6 text-right">
                                Usuario: <? echo $especificacion["usuario"]; ?>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-4">
                            <?
                            echo $especificacion["nombrediseno"] .
                            ($especificacion["serigrafia"]==1 ? "<br> - Serigrafia" : "") .  
                            ($especificacion["digital"]==1 ? "<br> - Digital" : "") . 
                            ($especificacion["bordado"]==1 ? "<br> - Bordado" : "") .  
                            ($especificacion["especificaciones"]!="" ? "<br> - Especificaciones: " . $especificacion["especificaciones"] : "");
                            ?>
                            </div>
                            <div class="col-4">
                            <?
                            foreach($especificacion["desgloses"] as $desglose){
                                echo " ".$desglose["cantidad"]." ".$desglose["producto"].(($desglose["talla"]!="") ? " | Talla: ".$desglose["talla"] : "").(($desglose["color"]!="") ? (($desglose["talla"]!="") ? ", " : " | ")."Color: ".$desglose["color"] : "")."<br>";
                            }
                            ?>
                            </div>
                            <div class="col-4">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <i class="fas fa-<? echo $iconoalmacen; ?>-circle text-<? echo $statusalmacen; ?>"></i><br>
                                        <small>Almacen</small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <i class="fas fa-<? echo $iconodiseno; ?>-circle text-<? echo $statusdiseno; ?>"></i><br>
                                        <small>Diseño</small>
                                    </div>
                                    <div class="col-4 text-center">
                                        <i class="fas fa-<? echo $iconoproduccion; ?>-circle text-<? echo $statusproduccion; ?>"></i><br>
                                        <small>Producción</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- imagenes -->
                        <?
                        if ((file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen1"]) and $especificacion["imagen1"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen2"]) and $especificacion["imagen2"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen3"]) and $especificacion["imagen3"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen4"]) and $especificacion["imagen4"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen5"]) and $especificacion["imagen5"] != "")) {
                            ?>
                            <div class="row">
                                <div class="col-6">
                                <label for=""><b>Muestras: </b></label>
                                    <div class="row">
                                        <?
                                        if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen1"]) and $especificacion["imagen1"] != "") {
                                            ?>
                                            <div class="col-2">
                                                <a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/<? echo $especificacion["imagen1"]; ?>" data-fancybox="gallery<? echo $especificacion["idespecificacion"]; ?>"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/<? echo $especificacion["imagen1"]; ?>" alt="" width="100%" height="50px"></a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                        <?
                                        if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen2"]) and $especificacion["imagen2"] != "") {
                                            ?>
                                            <div class="col-2">
                                                <a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/<? echo $especificacion["imagen2"]; ?>" data-fancybox="gallery<? echo $especificacion["idespecificacion"]; ?>"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<? echo $especificacion["idespecificacion"] ?>/<? echo $especificacion["imagen2"] ?>" alt="" width="100%" height="50px"></a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                        <?
                                        if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen3"]) and $especificacion["imagen3"] != "") {
                                            ?>
                                            <div class="col-2">
                                                <a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/<? echo $especificacion["imagen3"]; ?>" data-fancybox="gallery<? echo $especificacion["idespecificacion"]; ?>"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<? echo $especificacion["idespecificacion"] ?>/<? echo $especificacion["imagen3"] ?>" alt="" width="100%" height="50px"></a>
        
                                            </div>
                                            <?
                                        }
                                        ?>
                                        <?
                                        if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen4"]) and $especificacion["imagen4"] != "") {
                                            ?>
                                            <div class="col-2">
                                                <a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/<? echo $especificacion["imagen4"]; ?>" data-fancybox="gallery<? echo $especificacion["idespecificacion"]; ?>"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<? echo $especificacion["idespecificacion"] ?>/<? echo $especificacion["imagen4"] ?>" alt="" width="100%" height="50px"></a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                        <?
                                        if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/".$especificacion["imagen5"]) and $especificacion["imagen5"] != "") {
                                            ?>
                                            <div class="col-2">
                                                <a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/<? echo $especificacion["imagen5"]; ?>" data-fancybox="gallery<? echo $especificacion["idespecificacion"]; ?>"><img src="http<? echo (($_SERVER["HTTPS"]!="") ? "s" : "")."://".$_SERVER["SERVER_NAME"]; ?>/imagenes/especificaciones/<? echo $especificacion["idespecificacion"] ?>/<? echo $especificacion["imagen5"] ?>" alt="" width="100%" height="50px"></a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                        <!-- archivos -->
                        <?
                        if ((file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo1"]) and $especificacion["archivo1"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo2"]) and $especificacion["archivo2"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo3"]) and $especificacion["archivo3"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo4"]) and $especificacion["archivo4"] != "") or
                        (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo5"]) and $especificacion["archivo5"] != "")) {
                            ?>
                            <div class="row" style="margin-top:10px;">
                                <div class="col-6">
                                    <label for=""><b>Archivos: </b></label>
                                    <br>
                                    <?
                                    if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo1"]) and $especificacion["archivo1"] != "") {
                                        ?>
                                        
                                            <a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/archivos/<? echo $especificacion["archivo1"]; ?>" target="_blank"><? echo $especificacion["archivo1"]; ?></a>
                                        <?
                                    }
                                    if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo2"]) and $especificacion["archivo2"] != "") {
                                        ?>
                                        
                                            |&nbsp;<a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/archivos/<? echo $especificacion["archivo2"]; ?>" target="_blank"><? echo $especificacion["archivo2"]; ?></a>
                                        <?
                                    }
                                    if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo3"]) and $especificacion["archivo3"] != "") {
                                        ?>
                                        
                                            |&nbsp;<a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/archivos/<? echo $especificacion["archivo3"]; ?>" target="_blank"><? echo $especificacion["archivo3"]; ?></a>
                                        <?
                                    }
                                    if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo4"]) and $especificacion["archivo4"] != "") {
                                        ?>
                                        
                                            |&nbsp;<a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/archivos/<? echo $especificacion["archivo4"]; ?>" target="_blank"><? echo $especificacion["archivo4"]; ?></a>
                                        <?
                                    }
                                    if (file_exists("../../imagenes/especificaciones/".$especificacion["idespecificacion"]."/archivos/".$especificacion["archivo5"]) and $especificacion["archivo5"] != "") {
                                        ?>
                                            |&nbsp;<a href="/imagenes/especificaciones/<? echo $especificacion["idespecificacion"]; ?>/archivos/<? echo $especificacion["archivo5"]; ?>" target="_blank"><? echo $especificacion["archivo5"]; ?></a>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                        <!-- fin archivos -->
                    </div>
                </div>
                <?
            }
        }else{
        ?>
        <div class="row"><div class="col-xs-12 col-md-12 text-center"><? echo $especificaciones["mensaje"]; ?></div></div>
        <?
        }
        ?>
        </div>           
    </div>

    <div class="box">
        <div class="box-header text-center"><b>Compras del Pedido #<? echo $pedido["idpedido"]; ?></b></div>

        <div class="box-body">
            <?
            $productos = mysqli_query($con,"select * from tsolicitudescompra where idpedido='".$pedido["idpedido"]."'");
            if(mysqli_num_rows($productos)>0){
            ?>
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Cantidad</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    while($producto = mysqli_fetch_assoc($productos)){
                        $nombreproducto = ($producto["idproducto"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$producto["idproducto"]."'"))["nombre"] : $producto["producto"];
                        $nombrecolor = ($producto["idcolor"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$producto["idcolor"]."'"))["nombre"] : $producto["color"];
                        $nombretalla = ($producto["idtalla"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$producto["idtalla"]."'"))["nombre"] : $producto["talla"];
                        ?>
                        <tr>
                            <td><? echo $nombreproducto; ?></td>
                            <td><? echo $nombretalla; ?></td>
                            <td><? echo $nombrecolor; ?></td>
                            <td><? echo $producto["cantidad"]; ?></td>
                            <td>
                                <?php
                                $compraproducto = mysqli_fetch_assoc(mysqli_query($con,"select * from trcompraproductos where idsolicitudcompra = '".$producto["idsolicitudcompra"]."'"));
                                if($compraproducto["idcompraproducto"]>0){
                                    if($compraproducto["cantidad_recibida"]==0){
                                        echo "Sin Recibir";
                                    }else{
                                        echo "Recibido";
                                    }
                                }else{
                                    echo "Sin Comprar";
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
            }else{
            ?>
            No se realizaron compras para este pedido.
            <?
            }
            ?>            
        </div>
    </div>

</div>
<?
}else{
?>
<div class="container" style="padding-left: 0px; padding-right: 0px;padding-top:25px;">
    <div class="row"><div class="col-xs-12 col-md-12 text-center"><? echo $pedido["mensaje"]; ?></div></div>
</div>
<?
}
?>
</body>
</html>