<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../2cnytm029mp3r/cm293uc5904uh.php");
include("../vm39845um223u/qxom385u3mfg3.php");

// cambiar el status de la especificacionproducto 
// mostrar el texto "terminado"
// verificar si el producto al que se le ha cambiado el estado, era el último de la especificacion. Si es el caso, se actualiza statusproduccion a 2

mysqli_query($con,"update trespecificacionesproductos set status=2, fecha_fin_produccion ='".date("Y-m-d H:i:s")."' where idespecificacionproducto='".$_POST["idespecificacionproducto"]."'");

$idespecificacion = mysqli_fetch_assoc(mysqli_query($con,"select * from trespecificacionesproductos where idespecificacionproducto='".$_POST["idespecificacionproducto"]."'"))["idespecificacion"];

$tipoterminado = "0";
if (mysqli_num_rows(mysqli_query($con,"select * from trespecificacionesproductos where idespecificacion='".$idespecificacion."' and status!=2"))==0){
    // falta actualizar algun otro campo?
    mysqli_query($con,"update tespecificaciones set statusproduccion=2, status=2 where idespecificacion='".$idespecificacion."'");

    $idcliente = $_POST["idcliente"];

    if (mysqli_num_rows(mysqli_query($con,"select * from tespecificaciones where status!=2 and idespecificacion in (select idespecificacion from trespecificacionesproductos where idpedidoproducto in (select idpedidoproducto from trpedidoproductos where idpedido in (select idpedido from tpedidos where idcliente in (select idcliente from tclientes where idcliente='".$idcliente."'))))"))==0) {
        $tipoterminado = "2";
    }else {
        $tipoterminado = "1";
    }
}

echo "Terminado|".$tipoterminado;