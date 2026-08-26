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

// Cuando ya no hay CFDI que cancelar ante el SAT (el pago nunca se timbró, o su complemento
// ya se canceló) este botón revierte el pago, y eso borra los tickets de caja que generó.
// Solo en ese caso se muestra qué se va a eliminar
$revierte = ($pago["result"] == "success") && (empty($pago["pago"]["uuid"]) || $pago["pago"]["status"] == 4);
$tickets = $revierte ? $p->getTicketsPago($_GET["idpago"]) : array();

$cortescerrados = false;
foreach($tickets as $ticket){
    if($ticket["statuscorte"] != "A"){
        $cortescerrados = true;
    }
}
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
        <?
        if($revierte){
        ?>
        <div class="form-group">
            <label>Se revertirá</label>
            <ul class="mb-0">
                <li>El abono de cada pedido que cubrió este pago.</li>
                <li>El saldo que su complemento amortizó a cada factura.</li>
                <? if(!empty($tickets)){ ?>
                <li>Los tickets de caja que generó, con sus formas de pago.</li>
                <? } ?>
            </ul>
        </div>
        <? if(!empty($tickets)){ ?>
        <div class="form-group">
            <label>Tickets de caja que se eliminarán</label>
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Sucursal</th>
                        <th class="text-right">Monto</th>
                        <th>Corte</th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach($tickets as $ticket){ ?>
                    <tr>
                        <td>#<?= $ticket["folio"] ?></td>
                        <td><?= $ticket["sucursal"] ?></td>
                        <td class="text-right">$<?= number_format($ticket["total"],2) ?></td>
                        <td><?= ($ticket["statuscorte"] == "A") ? "Abierto" : "Cerrado" ?></td>
                    </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>
        <? } ?>
        <? if($cortescerrados){ ?>
        <div class="alert alert-warning">
            <strong>Atención:</strong> uno o más de estos tickets pertenecen a un corte que ya fue cerrado y arqueado.
            Al eliminarlos, el reporte de ese corte dejará de cuadrar contra el arqueo del día.
        </div>
        <? } ?>
        <? if(empty($tickets)){ ?>
        <div class="alert alert-warning">
            <strong>Atención:</strong> no se encontró el ticket de caja asociado a este pago.
            Se revertirán los pedidos y las facturas, pero el ticket tendrás que revisarlo manualmente.
        </div>
        <? } ?>
        <?
        }
        ?>
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
