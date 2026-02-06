<?
include("../../vm39845um223u/c91ktn24g7if5u.php");
include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// usar el idproducto para recuperar todos los colores y tallas disponibles del producto
$colores = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idcolor!=0 group by idcolor");
// $tallas = mysqli_query($con,"select * from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idtalla!=0 group by idtalla");
$tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$_POST["idproducto"]."' and idtalla!=0 group by idtalla) order by posicion");

if (mysqli_num_rows($colores)==1 and mysqli_num_rows($tallas)==1) {
    $color = mysqli_fetch_assoc($colores);
    $talla = mysqli_fetch_assoc($tallas);
    if(mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$_POST["idproducto"]."'"))["tarjetaregalo"]==0){
        echo "UNCOLORYTALLA|" . $color["idcolor"] . "-" . $nombre . "|" . $talla["idtalla"] . "-" . $nombre;
    }else{
        echo "TARJETAREGALO|" . $color["idcolor"] . "-" . $nombre . "|" . $talla["idtalla"] . "-" . $nombre;
    }
}else if (mysqli_num_rows($colores)>0) {
    echo "OK|";
    while($color = mysqli_fetch_assoc($colores)){
        $nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$color["idcolor"]."'"))["nombre"];
        echo $color["idcolor"] . "-" . $nombre . ",";
    }
    echo "|";
    while($talla = mysqli_fetch_assoc($tallas)){
        $nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$talla["idtalla"]."'"))["nombre"];
        echo $talla["idtalla"] . "-" . $nombre . ",";
    }
}else {
    echo "ERROR";
}
?>