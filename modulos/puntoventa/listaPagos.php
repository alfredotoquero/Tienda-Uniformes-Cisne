<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

if ($_POST["accion"]=="agregar") {
    if($_POST["formapago"]==4){
        $codigo = explode("-",$_POST["codigo"]);
        $query = "
        select
            *
        from
            tproductos
        where
            codigobarras = '".$codigo[0]."'";
        $producto = mysqli_fetch_assoc(mysqli_query($con,$query));
        if($producto["idproducto"]>0){

            $query = "
            select
                *
            from
                ttarjetasregalo
            where
                codigo = '".$_POST["codigo"]."' and
                activa = 1 and
                vigencia >= '".date("Y-m-d")."'";

            if(mysqli_num_rows(mysqli_query($con,$query))==1){
                mysqli_query($con,"insert into tformaspagotickettmp (idvendedor,idformapago,monto,codigo) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$_POST["formapago"]."','".$producto["precio"]."','".$_POST["codigo"]."')");
            }else{
                $query = "
                select
                    *
                from
                    ttarjetasregalo
                where
                    codigo = '".$_POST["codigo"]."'";
                $tarjeta = mysqli_query($con,$query);
                if(mysqli_num_rows(mysqli_query($con,$query))==1){
                    $tarjeta = mysqli_fetch_assoc($tarjeta);
                    if($tarjeta["activa"]==0){
                        $errortarjetaregaloutilizada = true;
                    }else if($tarjeta["vigencia"]<date("Y-m-d")){
                        $errortarjetaregalovencida = true;
                    }
                }else{
                    $errortarjetaregalonovendida = true;
                }
            }
        }
    }else{
        mysqli_query($con,"insert into tformaspagotickettmp (idvendedor,idformapago,monto) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$_POST["formapago"]."','".$_POST["monto"]."')");
    }
}

if ($_POST["accion"]=="eliminar") {
    mysqli_query($con,"delete from tformaspagotickettmp where idtmp='".$_POST["idpartida"]."'");
}

if ($_POST["accion"]=="mostrar") {
    // cada vez que se abra la ventana, deben borrarse los pagos anteriores que se hayan quedado
    mysqli_query($con,"delete from tformaspagotickettmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
}

$partidas = mysqli_query($con,"select * from tformaspagotickettmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");

if (mysqli_num_rows($partidas)>0) {
    ?>

    <!-- <div class="box-header">
        Lista de pagos agregados
    </div> -->

    <div class="box-body" >
        <div class="table-responsive">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th width="300" style="text-align:left;">Forma de pago</th>
                        <th width="100">Cantidad</th>
                        <th width="10"></th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    $total = 0;
                    while($partida = mysqli_fetch_assoc($partidas)){
                        $formapago = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatformaspago where idformapago = '".$partida["idformapago"]."'"));
                        $total += (float)(($partida["idformapago"]=="2" ? $formapago["pesos"] : 1)*$partida["monto"]);
                        ?>
                        <tr>
                            <td align="left"><? echo $formapago["nombre"]; ?></td>
                            <td>$<? echo number_format($partida["monto"],2); ?></td>

                            <td align="right"><a href="javascript:;" onClick="eliminarPago(<? echo $partida["idtmp"]; ?>);"><i class="fas fa-times"></i></a></td>
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
    $total = 0;
    ?>
    <div class="box-header">
    </div>
    <div class="box-body">
        <center><b><p>--No hay pagos agregados--</p></b></center>
    </div>
    <?
}
$tipocambiousd = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatformaspago where idformapago=2"))["pesos"];
$totalnegativo = mysqli_fetch_assoc(mysqli_query($con,"select if(abs(sum(total))>0,abs(sum(total)),0) as total from trcuentaproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and total < 0"))["total"];
$totalnegativo = ($totalnegativo>0) ? $totalnegativo*(-1) : 0;
?>

<script>
// calcular restante por cubrir
var totalPagos = <? echo $total; ?>;
var totalNegativo = <? echo ($totalnegativo<0) ? $totalnegativo : 0; ?>;
// var total = $("#total").val();
var total = Number($("#lblTotalCobrar").html().replace(/[^\d.]/g, ""));
var restante = total - totalPagos;
var cambio = (totalNegativo==0 ? total-totalPagos : 0);
cambio = (cambio < 0 ? cambio*(-1) : 0);
restante = (restante < 0 ? 0 : restante);
// alert("restante: " + restante);
$("#lblRestante").html('$' + formatMoney(restante));
$("#lblRestanteUSD").html('$' + formatMoney(restante/<? echo $tipocambiousd; ?>));
$("#cambio").val(cambio);
<? if($errortarjetaregaloutilizada){ ?>
alert("ERROR: La tarjeta de regalo ya fue utilizada.");
<? } ?>
<? if($errortarjetaregalonovendida){ ?>
alert("ERROR: La tarjeta de regalo no ha sido vendida aún.");
<? } ?>
<? if($errortarjetaregalovencida){ ?>
alert("ERROR: La tarjeta de regalo ha expirado.");
<? } ?>
</script>