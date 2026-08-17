<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/SAT.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");

$p = new Pagos();
$sat = new SAT();

$pago = $p->getPago(array(
    "idpago" => $_GET["idpago"]
));

$motivoscancelacion = $sat->obtenerMotivosCancelacion()["motivoscancelacion"];
?>
<div style="width:500px;">
    <?
    if($pago["result"]=="success"){
        $pago = $pago["pago"];
    ?>
    <div class="row">
        <div class="col-12">
            <h4>Cancelar pago <?= $pago["serie"]."-".$pago["folio"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formCancelarPago" name="formCancelarPago">
        <input type="hidden" name="controlador" id="controlador" value="pagos">
        <input type="hidden" name="proceso" id="proceso" value="cancelarPago">
        <input type="hidden" name="idpago" id="idpago" value="<?= $_GET["idpago"] ?>">
        <div class="form-group">
            <label>Cliente</label><br>
            <?= $pago["cliente"] ?>
        </div>
        <div class="form-group">
            <label>Fecha</label><br>
            <?= $p->fecha_formateada($pago["fecha"], false) ?>
        </div>
        <div class="form-group">
            <label for="slcMotivoCancelacion">Motivo de cancelación<span>*</span></label>
            <select class="form-control" name="slcMotivoCancelacion" id="slcMotivoCancelacion" onchange="validarMotivoCancelacionPago()">
                <option value="0">--Seleccionar--</option>
                <?
                foreach($motivoscancelacion as $motivocancelacion){
                ?>
                <option value="<?= $motivocancelacion["idmotivo"] ?>" data-uuid="<?= $motivocancelacion["requiere_uuid"] ?>"><?= $motivocancelacion["clave"]." - ".$motivocancelacion["descripcion"] ?></option>
                <?
                }
                ?>
            </select>
        </div>
        <div id="divUUIDPago" style="display:none;">
            <div class="form-group">
                <label for="txtUUID">UUID de sustitución<span>*</span></label>
                <input type="text" class="form-control" name="txtUUID" id="txtUUID" placeholder="Ingresa el UUID de sustitución" autocomplete="off" maxlength="36">
            </div>
        </div>
        <button type="button" onclick="confirmarCancelarPago();" class="btn btn-danger">Cancelar pago</button>
    </form>
    <?
    }else{
    ?>
    <div class="alert alert-danger"><?= $pago["mensaje"] ?></div>
    <?
    }
    ?>
</div>
<script>
document.addEventListener('input', function (e) {
    if (e.target.id === 'txtUUID') {
        e.target.value = e.target.value.toUpperCase();
    }
});

function validarMotivoCancelacionPago(){
    if($("#slcMotivoCancelacion option:selected").data("uuid")==1){
        $("#divUUIDPago").show();
    }else{
        $("#divUUIDPago").hide();
        $("#txtUUID").val("");
    }
}

function confirmarCancelarPago(){
    if($("#slcMotivoCancelacion").val()=="0"){
        Swal.fire({type:"warning", title:"Atención", text:"Debes seleccionar el motivo de cancelación"});
        return;
    }
    if($("#divUUIDPago").is(":visible") && $("#txtUUID").val().trim()==""){
        Swal.fire({type:"warning", title:"Atención", text:"Debes ingresar el UUID de sustitución"});
        return;
    }

    Swal.fire({
        type: "question",
        title: "Confirmar",
        text: "¿Deseas cancelar este pago?",
        showCancelButton: true,
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No"
    }).then(function(result){
        if(!result.value) return;

        $.ajax({
            url: "/assets/php/controladores/pagos.php",
            method: "POST",
            data: $("#formCancelarPago").serialize(),
            dataType: "json",
            success: function(res){
                if(res.success){
                    Swal.fire({type:"success", title:"Éxito", text:res.message}).then(function(){
                        $.fancybox.close();
                        App.modulos.pagos();
                    });
                }else{
                    Swal.fire({type:"error", title:"Error", text:res.message});
                }
            },
            error: function(){
                Swal.fire({type:"error", title:"Error", text:"No se pudo conectar con el servidor"});
            }
        });
    });
}
</script>
