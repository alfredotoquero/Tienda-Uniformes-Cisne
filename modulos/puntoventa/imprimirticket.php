<?php
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");
include("../../assets/php/libs/num2letras.php");

function limpiarString($string)
{
 
    $string = trim($string);
 
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    /*$string = str_replace(
        array("\", "¨", "º", "-", "~",
             "#", "@", "|", "!", """,
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", " "),
        '',
        $string
    );*/
 
 
    return $string;
}

$impresora = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))["impresora"];
$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3sucursal"]."'"));

$infoticket = mysqli_fetch_assoc(mysqli_query($con,"select * from ttickets where idticket = '".$_POST["idticket"]."'"));
$infocuenta = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta = '".$infoticket["idcuenta"]."'"));
$infopedido = mysqli_fetch_assoc(mysqli_query($con,"select * from vpedidos where idpedido = '".$infoticket["idpedido"]."'"));

if($infocuenta["idcuenta"]>0){
    $subtotal = $infocuenta["subtotal"];
    $iva = $infocuenta["iva"];
    $total = $infocuenta["total"];
    $abonado = $infocuenta["abonado"];
}else{
    $subtotal = $infopedido["subtotal"];
    $iva = $infopedido["iva"];
    $total = $infopedido["total"];
    $abonado = $infopedido["abonado"];
}
$abono = $infoticket["total"];
$cambio = $_POST["cambio"];

$idticket = str_pad($infoticket["folio"],7,"0",STR_PAD_LEFT);
$ticket .= date("d/m/Y",strtotime($infoticket["fecha"]))." ".date("h:i:s a",strtotime($infoticket["fecha"]))." ".$idticket;
?>
<script>
var imgData = '';
var imgDataQR = '';

var esc_init = "\x1B" + "\x40"; // initialize printer
var esc_p = "\x1B" + "\x70" + "\x30"; // open drawer
var gs_cut = "\x1D" + "\x56" + "\x4E"; // cut paper
var esc_a_l = "\x1B" + "\x61" + "\x30"; // align left
var esc_a_c = "\x1B" + "\x61" + "\x31"; // align center
var esc_a_r = "\x1B" + "\x61" + "\x32"; // align right
var esc_double = "\x1B" + "\x21" + "\x31"; // heading
var font_reset = "\x1B" + "\x21" + "\x02"; // styles off
var esc_ul_on = "\x1B" + "\x2D" + "\x31"; // underline on
var esc_bold_on = "\x1B" + "\x45" + "\x31"; // emphasis on
var esc_bold_off = "\x1B" + "\x45" + "\x30"; // emphasis off

<?
$query = "
select
    idparametro,
    parametro
from
    tparametros
where
    idparametro in ('leyendaWhatsapp','mensajeWhatsapp','telefonoWhatsapp','leyendasTicket') and
    parametro != ''";
$leyendas = mysqli_query($con,$query);

$imprimir_imagen = (file_exists($_SERVER["DOCUMENT_ROOT"]."/imagenes/sucursales/".$sucursal["imagen"]) && !is_null($sucursal["imagen"])) ? 1 : 0;
$imprimir_imagen += (mysqli_num_rows($leyendas)>0) ? 2 : 0;

$arrayLeyendas = array();
while($tmp = mysqli_fetch_assoc($leyendas)){
    $arrayLeyendas[$tmp["idparametro"]] = $tmp["parametro"];
}

if($imprimir_imagen>0){
    ?>
    imgQR = new Image();
    imgQR.onload = function () {
        // Create an empty canvas element
        //var canvas = document.createElement("canvas");
        var canvas = document.createElement('canvas');
        canvas.width = imgQR.width;
        canvas.height = imgQR.height;
        // Copy the image contents to the canvas
        var ctx = canvas.getContext("2d");
        ctx.drawImage(imgQR, 0, 0);
        // get image slices and append commands
        var bytedata = esc_init + esc_a_c + getESCPImageSlices(ctx, canvas) + font_reset;
        //alert(bytedata);
        imgDataQR = bytedata;

        imprimir(imgData,imgDataQR);
    };

    img = new Image();
    img.onload = function () {
        // Create an empty canvas element
        //var canvas = document.createElement("canvas");
        var canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        // Copy the image contents to the canvas
        var ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0);
        // get image slices and append commands
        var bytedata = esc_init + esc_a_c + getESCPImageSlices(ctx, canvas) + font_reset;
        //alert(bytedata);
        imgData = bytedata;

        <? if($imprimir_imagen==1){ ?>
        imprimir(imgData,imgDataQR);
        <? }else{ ?>
        imgQR.src = 'http<? echo (($_SERVER["HTTPS"]!="") ? "s" : ""); ?>://<? echo $_SERVER["HTTP_HOST"]; ?>/modulos/puntoventa/qr.php?url=https://wa.me/52<?= $arrayLeyendas["telefonoWhatsapp"] ?>?text=<?= urlencode($arrayLeyendas["mensajeWhatsapp"]) ?>';
        <? } ?>
    };
    <? if($imprimir_imagen==1 || $imprimir_imagen==3){ ?>
    img.src = 'http<? echo (($_SERVER["HTTPS"]!="") ? "s" : ""); ?>://<? echo $_SERVER["HTTP_HOST"]; ?>/imagenes/sucursales/<? echo $sucursal["imagen"]; ?>';
    <? }else{ ?>
    imgQR.src = 'http<? echo (($_SERVER["HTTPS"]!="") ? "s" : ""); ?>://<? echo $_SERVER["HTTP_HOST"]; ?>/modulos/puntoventa/qr.php?url=https://wa.me/52<?= $arrayLeyendas["telefonoWhatsapp"] ?>?text=<?= $arrayLeyendas["mensajeWhatsapp"] ?>';
    <?
    }
}else{
?>
imprimir();
<?
}
?>

function imprimir(imagen = null,imagenQR = null){
    var data = "";

    <?
    if($_POST["tipo"]==2){
    ?>
    data += esc_init + esc_a_c + "APARTADO <? echo $idticket;?>\n\n<? echo limpiarString(strtoupper($infocuenta["nombrecuenta"])); ?>\n<? echo limpiarString(strtoupper($infoticket["telefono"])); ?>\n\n";
    <?
    }
    ?>

    <?
    if($_POST["tipo"]==4){
    ?>
    data += esc_init + esc_a_c + "PEDIDO <? echo $infopedido["idpedido"];?>\n\n<? echo limpiarString(strtoupper($infopedido["cliente"])); ?>";
    <?
    }
    ?>

    <?
    if($infopedido["correocliente"]!=""){
    ?>
    data += "\n<? echo $infopedido["correocliente"];?>";
    <?
    }
    ?>

    <?
    if($infopedido["telefonocliente"]!=""){
    ?>
    data += "\n<? echo $infopedido["telefonocliente"];?>";
    <?
    }
    ?>

    data += "\n\n";
    if(imagen!=null){
        data += imagen;
    }
    data += esc_init + esc_a_c + "<? echo limpiarString(strtoupper($sucursal["nombre"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["calle"]." No. ".$sucursal["numero"].", ".$sucursal["colonia"])); ?>\n";
    data += "<? echo limpiarString(strtoupper("C.P. ".$sucursal["codigopostal"].", ".$sucursal["ciudad"])); ?>\n";
    data += "<? echo limpiarString(strtoupper("Teléfono ".$sucursal["telefono"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["razonsocial"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["rfc"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["regimen"])); ?>\n";
    data += "<? echo $ticket; ?>\n\n";

    data += "    PRODUCTO                       IMPORTE";

    <?
    $articulos = 0;
    $existe_personalizacion = false;
    $impresorascuenta = array();
    switch($_POST["tipo"]){
        case 1:
            $descuento = 0;
            $porcentaje_descuento = 0;
            $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$infoticket["idcuenta"]."'");
            while($partida = mysqli_fetch_assoc($partidas)){

                $articulos += $partida["cantidad"];
                $cantidad = $partida["cantidad"];
                $nombre = limpiarString($partida["producto"]." (TALLA: ".$partida["talla"]." COLOR: ".$partida["color"].")");
                $precio = "$".number_format($partida["total"],2);
                $descuento_partida = ($partida["descuento"]>0) ? (($partida["total"]/(1-($partida["descuento"]/100))) - $partida["total"]) : 0;
                $importe_sin_descuento = "$".number_format($partida["total"]+$descuento_partida,2);
                $importe = "$".number_format($partida["total"],2);
                $descuento += $descuento_partida;
                $porcentaje_descuento = $partida["descuento"];
                ?>
                data += "<? echo str_pad($cantidad,3," ",STR_PAD_RIGHT); ?>";
                <?
                $tamano = 0;
                $renglon = 1;
                $palabras = explode(" ",$nombre);
                for($i = 0;$i<count($palabras);$i++){
                    if(((int)$tamano + (int)strlen($palabras[$i]))<=28){
                    ?>
                    data += " <? echo $palabras[$i]; ?>";
                    <?
                        $tamano += (int)strlen($palabras[$i]) + (int)1;
                    }else{
                        if($renglon==1){
                        ?>
                        data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe_sin_descuento,9," ",STR_PAD_LEFT); ?>";
                        <?
                            $renglon++;
                        }
                    ?>
                    data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                    <?
                        $tamano = strlen($palabras[$i]);
                    }
                }
                if($renglon==1){
                ?>
                data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe_sin_descuento,9," ",STR_PAD_LEFT); ?>";
                <?
                }
                ?>
                data += "\n";
                <?
                $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
                while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                    $existe_personalizacion = true;
                    $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                    $tamano = 0;
                    $renglon = 1;
                    $textopersonalizacion = limpiarString("- " . str_replace("\n\r"," ",$categoria["nombre"]) . ": " . str_replace("\n\r"," ",$personalizacion["personalizacion"]));
                    $palabras = explode(" ",$textopersonalizacion);
                    ?>
                    data += "   ";
                    <?
                    for($i = 0;$i<count($palabras);$i++){
                        if(((int)$tamano + (int)strlen($palabras[$i]))<=26){
                            ?>
                            data += " <? echo $palabras[$i]; ?>";
                            <?
                            $tamano += (int)strlen($palabras[$i]) + (int)1;
                        }else{
                            ?>
                            data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                            <?
                            $tamano = strlen($palabras[$i]);
                        }
                    }
                    ?>
                    data += "\n";
                    <?php
                }
                if($descuento_partida>0){
                ?>
                data += esc_a_l + "    <?= str_pad("Descuento ".$partida["descuento"]."%",27," ",STR_PAD_LEFT).str_pad("-$".number_format($descuento_partida,2),10," ",STR_PAD_LEFT) ?>";
                data += "\n    <?= str_pad(" ",28," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT) ?>";
                <?
                }
            }
            ?>
            data += "\n\n";
            data += esc_a_r + "SUBTOTAL <? echo "$".number_format($subtotal,2); ?>\n";
            data += esc_a_r + "IVA <? echo "$".number_format($iva,2); ?>\n";
            data += esc_a_r + "TOTAL <? echo "$".number_format($total,2); ?>\n\n";
            <? if($descuento>0){ ?>
            data += esc_a_r + "USTED AHORRO (<?= $porcentaje_descuento ?>%) <? echo "$".number_format($descuento,2); ?>\n\n";
            <? } ?>
            data += "SU PAGO\n";
            <?
            $formaspago = mysqli_query($con,"select * from tformaspagoticket where idticket = '".$infoticket["idticket"]."'");
            while($formapago = mysqli_fetch_assoc($formaspago)){
                $metodo = mysqli_fetch_assoc(mysqli_query($con,"select nombre from tcatformaspago where idformapago = '".$formapago["idformapago"]."'"))["nombre"];
            ?>
            data += "<? echo limpiarString($metodo)." $".number_format($formapago["montorecibido"],2); ?>\n";
            <?
            }
            ?>
            data += "\nCAMBIO <? echo "$".number_format($cambio,2); ?>\n\n";
            data += esc_a_c + "<? echo strtoupper(num2letras(number_format($total,2,'.',''))); ?>\n\n";
            data += "ARTICULOS: <? echo $articulos; ?>\n\n";
            data += "GRACIAS POR SU COMPRA\n";
            <?
        break;
        case 2:
            $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$infoticket["idcuenta"]."'");
            while($partida = mysqli_fetch_assoc($partidas)){

                $articulos += $partida["cantidad"];
                $cantidad = $partida["cantidad"];
                $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
                $nombre = limpiarString($partida["producto"]." (TALLA: ".$partida["talla"]." COLOR: ".$partida["color"].")");
                $precio = "$".number_format($partida["total"],2);
                $importe = "$".number_format($partida["total"],2);
                ?>
                data += "<? echo str_pad($cantidad,3," ",STR_PAD_RIGHT); ?>";
                <?
                $tamano = 0;
                $renglon = 1;
                $palabras = explode(" ",$nombre);
                for($i = 0;$i<count($palabras);$i++){
                    if(((int)$tamano + (int)strlen($palabras[$i]))<=28){
                    ?>
                    data += " <? echo $palabras[$i]; ?>";
                    <?
                        $tamano += (int)strlen($palabras[$i]) + (int)1;
                    }else{
                        if($renglon==1){
                        ?>
                        data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT); ?>";
                        <?
                            $renglon++;
                        }
                    ?>
                    data += "\n    <? echo $palabras[$i]; ?>";
                    <?
                        $tamano = strlen($palabras[$i]);
                    }
                }
                if($renglon==1){
                ?>
                data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT); ?>";
                <?
                }
                ?>
                data += "\n";
                <?
                $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
                while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                    $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                    $tamano = 0;
                    $renglon = 1;
                    $textopersonalizacion = limpiarString("- " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"]);
                    $palabras = explode(" ",$textopersonalizacion);
                    for($i = 0;$i<count($palabras);$i++){
                        if(((int)$tamano + (int)strlen($palabras[$i]))<=26){
                            ?>
                            data += " <? echo $palabras[$i]; ?>";
                            <?
                            $tamano += (int)strlen($palabras[$i]) + (int)1;
                        }else{
                            ?>
                            data += "\n      <? echo $palabras[$i]; ?>";
                            <?
                            $tamano = strlen($palabras[$i]);
                        }
                    }
                    ?>
                    data += "\n";
                    <?php
                }
            }
            ?>
            data += "\n\n";
            //relacionados a tcuentas
            data += esc_a_r + "SUBTOTAL <? echo "$".number_format($subtotal,2); ?>\n";
            data += esc_a_r + "IVA <? echo "$".number_format($iva,2); ?>\n";
            data += esc_a_r + "TOTAL <? echo "$".number_format($total,2); ?>\n";
            //relacionados a ttickets
            data += "ABONO <? echo "$".number_format($abono,2); ?>\n\n";
            data += "SU PAGO\n";
            <?
            $formaspago = mysqli_query($con,"select * from tformaspagoticket where idticket = '".$infoticket["idticket"]."'");
            while($formapago = mysqli_fetch_assoc($formaspago)){
                $metodo = mysqli_fetch_assoc(mysqli_query($con,"select nombre from tcatformaspago where idformapago = '".$formapago["idformapago"]."'"))["nombre"];
            ?>
            data += "<? echo limpiarString($metodo)." $".number_format($formapago["montorecibido"],2); ?>\n";
            <?
            }
            $saldo = $total - $abono;
            ?>
            //saldo = $totalcuenta - $total;
            data += "\nSALDO <? echo "$".number_format($saldo,2); ?>\n\n";
            data += "\nCAMBIO <? echo "$".number_format($cambio,2); ?>\n\n";
            data += esc_a_c + "<? echo strtoupper(num2letras(number_format($total,2,'.',''))); ?>\n\n";
            data += "ARTICULOS: <? echo $articulos; ?>\n\n";

            //agregar notas de apartado
            data += "<? echo limpiarString($infoticket["notas"]); ?>\n\n";

            data += "GRACIAS POR SU COMPRA\n";
            <?
        break;
        case 3:
            $tamano = 0;
            $renglon = 1;
            $palabras = explode(" ","Abono a apartado #".$infoticket["folio"]);
            for($i = 0;$i<count($palabras);$i++){
                if(((int)$tamano + (int)strlen($palabras[$i]))<=28){
                ?>
                data += " <? echo $palabras[$i]; ?>";
                <?
                    $tamano += (int)strlen($palabras[$i]) + (int)1;
                }else{
                    if($renglon==1){
                    ?>
                    data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT); ?>";
                    <?
                        $renglon++;
                    }
                ?>
                data += "\n    <? echo $palabras[$i]; ?>";
                <?
                    $tamano = strlen($palabras[$i]);
                }
            }
            ?>
            data += "\n\n";
            // relacionado a tcuentas
            data += esc_a_r + "SUBTOTAL <? echo "$".number_format($subtotal,2); ?>\n";
            data += esc_a_r + "IVA <? echo "$".number_format($iva,2); ?>\n";
            data += esc_a_r + "TOTAL <? echo "$".number_format($total,2); ?>\n";

            <?
            if($abonado-$abono>0){
            ?>
            data += "ANTICIPO <? echo "$".number_format($abonado-$abono,2); ?>\n\n";
            data += "LIQUIDACION <? echo "$".number_format($abono,2); ?>\n\n";
            <?
            }else{
            ?>
            data += "\n\nANTICIPO <? echo "$".number_format($abono,2); ?>\n\n";
            <?
            }
            ?>            
            data += "SU PAGO\n";
            <?
            $formaspago = mysqli_query($con,"select * from tformaspagoticket where idticket = '".$infoticket["idticket"]."'");
            while($formapago = mysqli_fetch_assoc($formaspago)){
                $metodo = mysqli_fetch_assoc(mysqli_query($con,"select nombre from tcatformaspago where idformapago = '".$formapago["idformapago"]."'"))["nombre"];
            ?>
            data += "<? echo limpiarString($metodo)." $".number_format($formapago["montorecibido"],2); ?>\n";
            <?
            }
            $saldo = $total - $abonado;
            ?>
            // saldo = $totalcuenta - $total;
            data += "\nSALDO <? echo "$".number_format($saldo,2); ?>\n\n";
            data += "\nCAMBIO <? echo "$".number_format($cambio,2); ?>\n\n";

            // agregar notas de apartado
            data += esc_a_l + "<? echo limpiarString($infoticket["notas"]); ?>\n\n";

            data += esc_a_c + "GRACIAS POR SU COMPRA\n";
            <?
        break;
        case 4:
            $partidas = mysqli_query($con,"select * from trcotizacionproductos where idpedido='".$infopedido["idpedido"]."' group by idproducto,producto order by idcotizacionproducto");
            if(mysqli_num_rows($partidas)>0){
                while($partida = mysqli_fetch_assoc($partidas)){

                    $producto = mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'");
                    if (mysqli_num_rows($producto)) {
                        $nombre = limpiarString(mysqli_fetch_assoc($producto)["nombre"]);
                    } else {
                        $nombre = limpiarString($partida["producto"]);
                    }
                    
                    $cantidad = $partida["cantidad"];
                    $importe = "$".number_format($partida["precio"]*$cantidad,2);

                    ?>
                    data += "<? echo str_pad($cantidad,3," ",STR_PAD_RIGHT); ?>";
                    <?
                    $tamano = 0;
                    $renglon = 1;
                    $palabras = explode(" ",$nombre);
                    for($i = 0;$i<count($palabras);$i++){
                        if(((int)$tamano + (int)strlen($palabras[$i]))<=28){
                        ?>
                        data += " <? echo $palabras[$i]; ?>";
                        <?
                            $tamano += (int)strlen($palabras[$i]) + (int)1;
                        }else{
                            if($renglon==1){
                            ?>
                            data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT); ?>";
                            <?
                                $renglon++;
                            }
                        ?>
                        data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                        <?
                            $tamano = strlen($palabras[$i]);
                        }
                    }
                    if($renglon==1){
                    ?>
                    data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT); ?>";
                    <?
                    }
                    ?>
                    data += "\n";
                    <?

                    $dcolores = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$infopedido["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idproducto!=0 group by idcolor");
                    while($dcolor = mysqli_fetch_assoc($dcolores)){
                    
                        $color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$dcolor["idcolor"]."'"))["nombre"];
                        $extras = limpiarString($color);
                    
                        $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$infopedido["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$dcolor["idcolor"]."'");
                        while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                            // talla
                            $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                            $extras .= limpiarString(" / " . $dtalla["cantidad"] . " - " . $talla);
                        }

                        $tamano = 0;
                        $renglon = 1;
                        $palabras = explode(" ",$extras);
                        for($i = 0;$i<count($palabras);$i++){
                            if(((int)$tamano + (int)strlen($palabras[$i]))<=26){
                                ?>
                                data += " <? echo $palabras[$i]; ?>";
                                <?
                                $tamano += (int)strlen($palabras[$i]) + (int)1;
                            }else{
                                ?>
                                data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                                <?
                                $tamano = strlen($palabras[$i]);
                            }
                        }
                        ?>
                        data += "\n";
                        <?php
                    }

                    $desgloses = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$infopedido["idpedido"]."' and idproducto=0 and producto='".$partida["producto"]."' group by color having color!='' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                    while ($desglose = mysqli_fetch_assoc($desgloses)) {
                    
                        $color = $desglose["color"];
                        $extras = limpiarString($color); 

                        $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$infopedido["idpedido"]."' and idproducto=0 and color='".$desglose["color"]."' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                        while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                            // talla
                            $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                            $extras .= limpiarString(" / " . $dtalla["cantidad"] . " - " . $talla);
                        }

                        $tamano = 0;
                        $renglon = 1;
                        $palabras = explode(" ",$extras);
                        for($i = 0;$i<count($palabras);$i++){
                            if(((int)$tamano + (int)strlen($palabras[$i]))<=26){
                                ?>
                                data += " <? echo $palabras[$i]; ?>";
                                <?
                                $tamano += (int)strlen($palabras[$i]) + (int)1;
                            }else{
                                ?>
                                data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                                <?
                                $tamano = strlen($palabras[$i]);
                            }
                        }
                        ?>
                        data += "\n";
                        <?php
                    }
                }
            }else{
                $tamano = 0;
                $renglon = 1;
                $palabras = explode(" ","Abono a pedido #".$infopedido["idpedido"]);
                for($i = 0;$i<count($palabras);$i++){
                    if(((int)$tamano + (int)strlen($palabras[$i]))<=28){
                    ?>
                    data += " <? echo $palabras[$i]; ?>";
                    <?
                        $tamano += (int)strlen($palabras[$i]) + (int)1;
                    }else{
                        if($renglon==1){
                        ?>
                        data += "<? echo str_pad(" ",(29-($tamano-1))," ",STR_PAD_RIGHT).str_pad($importe,9," ",STR_PAD_LEFT); ?>";
                        <?
                            $renglon++;
                        }
                    ?>
                    data += "\n    <? echo $palabras[$i]; ?>";
                    <?
                        $tamano = strlen($palabras[$i]);
                    }
                }
            }
            ?>
            data += "\n\n";
            // relacionado a tcuentas
            data += esc_a_r + "SUBTOTAL <? echo "$".number_format($subtotal,2); ?>\n";
            data += esc_a_r + "IVA <? echo "$".number_format($iva,2); ?>\n";
            data += esc_a_r + "TOTAL <? echo "$".number_format($total,2); ?>\n";
            
            <?
            if($abonado-$abono>0){
            ?>
            data += "ANTICIPO <? echo "$".number_format($abonado-$abono,2); ?>\n\n";
            data += "LIQUIDACION <? echo "$".number_format($abono,2); ?>\n\n";
            <?
            }else{
            ?>
            data += "\n\nANTICIPO <? echo "$".number_format($abono,2); ?>\n\n";
            <?
            }
            ?>

            data += "SU PAGO\n";
            <?
            $formaspago = mysqli_query($con,"select * from tformaspagoticket where idticket = '".$infoticket["idticket"]."'");
            while($formapago = mysqli_fetch_assoc($formaspago)){
                $metodo = mysqli_fetch_assoc(mysqli_query($con,"select nombre from tcatformaspago where idformapago = '".$formapago["idformapago"]."'"))["nombre"];
            ?>
            data += "<? echo limpiarString($metodo)." $".number_format($formapago["montorecibido"],2); ?>\n";
            <?
            }
            $saldo = $total - $abonado;
            ?>
            // saldo = $totalcuenta - $total;
            data += "\nSALDO <? echo "$".number_format($saldo,2); ?>\n\n";
            data += "\nCAMBIO <? echo "$".number_format($cambio,2); ?>\n\n";

            // agregar notas de apartado
            data += esc_a_l + "<? echo limpiarString($infoticket["notas"]); ?>\n\n";

            data += esc_a_c + "GRACIAS POR SU COMPRA\n";
            <?
        break;
        default:
        break;
    }
    if($imprimir_imagen>=2 && ($_POST["tipo"]==1 || $_POST["tipo"]==2)){
    ?>
    data += "\n\n";
    if(imagenQR!=null){
        data += imagenQR;
        data += "\n";
        <?
        $cadenas = explode("\r\n",$arrayLeyendas["leyendaWhatsapp"]);
        foreach($cadenas as $cadena){
            $renglones = array();
            $palabras = explode(" ",limpiarString($cadena));
            $i=0;
            foreach($palabras as $palabra){
                if((strlen($renglones[$i]) + strlen($palabra))<=41 && strlen($renglones[$i])>0){
                    $renglones[$i] .= " ";
                }else{
                    $i++;
                }
                $renglones[$i] .= $palabra;
            }
            foreach($renglones as $renglon){
            ?>
            data += esc_a_c + "<? echo $renglon; ?>\n";
            <?
            }
        }
        ?>
    }
    <?
    }

    if($arrayLeyendas["leyendasTicket"]!="" && ($_POST["tipo"]==1 || $_POST["tipo"]==2)){
    ?>
    data += "\n";
    <?
        $cadenas = explode("\r\n",$arrayLeyendas["leyendasTicket"]);
        foreach($cadenas as $cadena){
            $renglones = array();
            $palabras = explode(" ",limpiarString($cadena));
            $i=0;
            foreach($palabras as $palabra){
                if((strlen($renglones[$i]) + strlen($palabra))<=41 && strlen($renglones[$i])>0){
                    $renglones[$i] .= " ";
                }else{
                    $i++;
                }
                $renglones[$i] .= $palabra;
            }
            foreach($renglones as $renglon){
            ?>
            data += esc_a_l + "<? echo $renglon; ?>\n";
            <?
            }
        }
    }
    if($_POST["tipo"]==1 && $existe_personalizacion){
        ?>
        data += "\n\n\n\n";
        data += esc_init + esc_a_c + "ORDEN DE PRODUCCION\n\n";
        data += "    PRODUCTO";
        <?
        $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$infoticket["idcuenta"]."'");
        while($partida = mysqli_fetch_assoc($partidas)){
            $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
            if(mysqli_num_rows($personalizaciones)>0){
                $articulos += $partida["cantidad"];
                $cantidad = $partida["cantidad"];
                $nombre = limpiarString($partida["producto"]." (TALLA: ".$partida["talla"]." COLOR: ".$partida["color"].")");
                ?>
                data += "<? echo str_pad($cantidad,3," ",STR_PAD_RIGHT); ?>";
                <?
                $tamano = 0;
                $palabras = explode(" ",$nombre);
                for($i = 0;$i<count($palabras);$i++){
                    if(((int)$tamano + (int)strlen($palabras[$i]))<=28){
                        ?>
                        data += " <? echo $palabras[$i]; ?>";
                        <?
                        $tamano += (int)strlen($palabras[$i]) + (int)1;
                    }else{
                        ?>
                        data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                        <?
                        $tamano = strlen($palabras[$i]);
                    }
                }
                ?>
                data += "\n";
                <?
                while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                    $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                    $tamano = 0;
                    $renglon = 1;
                    $textopersonalizacion = limpiarString("- " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"]);
                    $palabras = explode(" ",$textopersonalizacion);
                    ?>
                    data += "   ";
                    <?
                    for($i = 0;$i<count($palabras);$i++){
                        if(((int)$tamano + (int)strlen($palabras[$i]))<=26){
                            ?>
                            data += " <? echo $palabras[$i]; ?>";
                            <?
                            $tamano += (int)strlen($palabras[$i]) + (int)1;
                        }else{
                            ?>
                            data += "\n" + esc_a_l + "    <? echo $palabras[$i]; ?>";
                            <?
                            $tamano = strlen($palabras[$i]);
                        }
                    }
                    ?>
                    data += "\n";
                    <?php
                }
            }
        }
    }
    ?>
    data += "\n\n\n\n\n\n\n\n\n\n\n" + gs_cut + "\r";

    webprint.printRaw(data,'<? echo str_replace("\\","\\\\",$impresora); ?>');
}

function getESCPImageSlices(context, canvas) {
	var width = canvas.width;
	var height = canvas.height;
	var nL = Math.round(width % 256);
	var nH = Math.round(height / 256);
	var dotDensity = 33;
	// read each pixel and put into a boolean array
	var imageData = context.getImageData(0, 0, width, height);
	imageData = imageData.data;
	// create a boolean array of pixels
	var pixArr = [];
	for (var pix = 0; pix < imageData.length; pix += 4) {
		pixArr.push((imageData[pix] == 0));
	}
	// create the byte array
	var final = [];
	// this function adds bytes to the array
	function appendBytes() {
		for (var i = 0; i < arguments.length; i++) {
			final.push(arguments[i]);
		}
	}
	// Set the line spacing to 24 dots, the height of each "stripe" of the image that we're drawing.
	appendBytes(0x1B, 0x33, 24);
	// Starting from x = 0, read 24 bits down. The offset variable keeps track of our global 'y'position in the image.
	// keep making these 24-dot stripes until we've executed past the height of the bitmap.
	var offset = 0;
	while (offset < height) {
		// append the ESCP bit image command
		appendBytes(0x1B, 0x2A, dotDensity, nL, nH);
		for (var x = 0; x < width; ++x) {
			// Remember, 24 dots = 24 bits = 3 bytes. The 'k' variable keeps track of which of those three bytes that we're currently scribbling into.
			for (var k = 0; k < 3; ++k) {
				var slice = 0;
				// The 'b' variable keeps track of which bit in the byte we're recording.
				for (var b = 0; b < 8; ++b) {
					// Calculate the y position that we're currently trying to draw.
					var y = (((offset / 8) + k) * 8) + b;
					// Calculate the location of the pixel we want in the bit array. It'll be at (y * width) + x.
					var i = (y * width) + x;
					// If the image (or this stripe of the image)
					// is shorter than 24 dots, pad with zero.
					var bit;
					if (pixArr.hasOwnProperty(i)) bit = pixArr[i] ? 0x01 : 0x00; else bit = 0x00;
					// Finally, store our bit in the byte that we're currently scribbling to. Our current 'b' is actually the exact
					// opposite of where we want it to be in the byte, so subtract it from 7, shift our bit into place in a temp
					// byte, and OR it with the target byte to get it into the final byte.
					slice |= bit << (7 - b);    // shift bit and record byte
				}
				// Phew! Write the damn byte to the buffer
				appendBytes(slice);
			}
		}
		// We're done with this 24-dot high pass. Render a newline to bump the print head down to the next line and keep on trucking.
		offset += 24;
		appendBytes(10);
	}
	// Restore the line spacing to the default of 30 dots.
	appendBytes(0x1B, 0x33, 30);
	// convert the array into a bytestring and return
	final = ArrayToByteStr(final);
	return final;
}
/**
 * @return {string}
 */
function ArrayToByteStr(array) {
	var s = '';
	for (var i = 0; i < array.length; i++) {
		s += String.fromCharCode(array[i]);
	}
	return s;
}
</script>