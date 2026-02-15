<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");

$arrayerror = array(
    "success"=>false,
    "message"=>"Acción no permitida."
);

try{
    $clasePagos = new Pagos();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["proceso"]){
        case "agregarPago":
            $respuesta = $clasePagos->agregarPago($_POST);
        break;
        default: $respuesta = $arrayerror; break;
    }
    
}catch(Exception $e){
    $respuesta = array(
        "success"=>false,
        "message"=>$e->getMessage()
    );
}finally{
    echo json_encode($respuesta,JSON_FORCE_OBJECT);
}
