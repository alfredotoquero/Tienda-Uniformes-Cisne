<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

mysqli_query($con,"update trcuentaproductos set status='E' where idcuentaproducto='".$_POST["idcuentaproducto"]."'");

if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_POST["idcuenta"]."' and status='P'"))==0) {
    mysqli_query($con,"update tcuentas set status='E' where idcuenta='".$_POST["idcuenta"]."'");
}

$partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_POST["idcuenta"]."'");

if (mysqli_num_rows($partidas)>0) {
    ?>
    <table class="table table-striped b-t">
        <thead>
            <tr>
                <th>Cantidad</th>
                <th>Nombre</th>
                <th>Descuento</th>
                <th>Total</th>
                <th style="width:50px;"></th>
            </tr>
        </thead>
        <tbody>
            <?
            $abonado = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_POST["idcuenta"]."'"))["abonado"];
            $dineroproductosentregados = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from trcuentaproductos where idcuenta='".$_POST["idcuenta"]."' and status='E'"))["total"];
            while($partida = mysqli_fetch_assoc($partidas)){
                $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
                ?>
                <tr>
                    <td align="center"><? echo $partida["cantidad"]; ?></td>
                    <td><? echo $producto["nombre"] . ($partida["status"]=="E" ? "(ENTREGADO)" : ""); ?></td>
                    <td><? echo $partida["descuento"] ?>%</td>
                    <td>$<? echo number_format($partida["total"],2); ?></td>
                    <td>
                        <?
                        if ($partida["status"]=="P") {
                            if ($abonado - $dineroproductosentregados >= $partida["total"]) {
                                ?>
                                <a href="javascript:;" onClick="entregarProducto(<? echo $partida["idcuentaproducto"]; ?>);" class="btn white">Entregar</a>
                                <?
                            }
                        } else {
                            echo ($partida["status"]=="E" ? "ENTREGADO" : "CANCELADO");
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


