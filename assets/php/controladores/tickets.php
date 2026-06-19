<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Tickets.php");

$arrayerror = array(
    "success"=>false,
    "message"=>"Acción no permitida."
);

try{
    $claseTickets = new Tickets();

    $idusuario = $_SESSION["usuario"]["idusuario"];
    $_POST["idusuario"] = $idusuario;

    switch($_POST["accion"]){
        case "facturar":
            if($_POST["authToken"]==$_SESSION["authToken"]){
                $respuesta = $claseTickets->facturarTicket($_POST);
            }else{
                $respuesta = $arrayerror;
            }
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
