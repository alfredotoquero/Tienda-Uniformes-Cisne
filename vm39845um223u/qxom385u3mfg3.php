<?
if($_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]!=NULL){
    //sino, calculamos el tiempo transcurrido
    $fechaGuardada = $_SESSION["v3nd3d0rpl4y3r4spvc1sn3Fecha"];
    $ahora = date("Y-n-j H:i:s");
    $tiempo_transcurrido = (strtotime($ahora)-strtotime($fechaGuardada));
    //comparamos el tiempo transcurrido
   	if($tiempo_transcurrido >= 7200) {
		//si pasaron 10 minutos o más
		unset($_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]);
		unset($_SESSION["v3nd3d0rpl4y3r4spvc1sn3Fecha"]);
		session_destroy(); // destruyo la sesión
		header("location: index.php");
    }else {
    	$_SESSION["v3nd3d0rpl4y3r4spvc1sn3Fecha"] = $ahora;
    }
}else{
	header("location: index.php");
}
?>
