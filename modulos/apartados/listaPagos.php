<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

if ($_POST["accion"]=="agregar") {
    mysqli_query($con,"insert into tformaspagotickettmp (idvendedor,idformapago,monto) values ('".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."','".$_POST["formapago"]."','".$_POST["monto"]."')");
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
                        $total += (float)(($partida["idformapago"]=="2" ? 18 : 1)*$partida["monto"]);
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
?>

<script>
// calcular restante por cubrir
var totalPagos = <? echo $total; ?>;
// var total = $("#total").val();
var total = Number($("#lblTotalCobrar").html().replace(/^\D+/g, ""));
var restante = total - totalPagos;
var cambio = total - totalPagos;
cambio = (cambio < 0 ? cambio*(-1) : 0);
restante = (restante < 0 ? 0 : restante);
// alert("restante: " + restante);
$("#lblRestante").html('$' + formatMoney(restante));
$("#lblRestanteUSD").html('$' + formatMoney(restante/18));
$("#cambio").val(cambio);
</script>