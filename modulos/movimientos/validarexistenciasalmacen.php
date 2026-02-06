<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// validar que todas las cantidades escritas (salida/traspaso) sean iguales o menores a las existencias disponibles correspondientes
// si hay al menos una combinación de color y talla que no cumpla la condición, no se puede registrar la operación

// recibo: idalmacen, idproducto y sus colores y tallas (tabla)
$correcto = true;
    // colores
    // $colores = mysqli_query($con,"select * from tcatcolores where idcolor in (select idcolor from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idalmacen='".$_POST["almacen"]."' group by idcolor)");
    $colores = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idalmacen='".$_POST["almacen"]."' group by idcolor");
    if (mysqli_num_rows($colores)>0) {
        while($color = mysqli_fetch_assoc($colores)){
            // $tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idalmacen='".$_POST["almacen"]."' group by idtalla) order by posicion");
            $tallas = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idalmacen='".$_POST["almacen"]."' group by idtalla");
            if (mysqli_num_rows($tallas)>0) {
                while ($talla = mysqli_fetch_assoc($tallas)) {
                    // si tiene algo escrito
                    if ($_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]>0) {
                        // validar
                        $existencia = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idcolor='".$color["idcolor"]."' and idtalla='".$talla["idtalla"]."' and idalmacen='".$_POST["almacen"]."'"))["existencia"];
                        if ($existencia<$_POST["txtCantidad".$color["idcolor"]."-".$talla["idtalla"]]) {
                            $correcto = false;
                            // $nombrecolor = $color["nombre"];
                            // $nombretalla = $talla["nombre"];
                            $nombrecolor = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$color["idcolor"]."'"))["nombre"];
                            $nombretalla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$talla["idtalla"]."'"))["nombre"];
                            break 2;
                        }
                    }
                }
            }else{
                $correcto = false;
            }
        }
    }else{
        $correcto = false;
    }

if ($correcto) {
    echo "OK";
}else {
    echo "ERROR|" . $nombrecolor . "|" . $nombretalla;
}
?>