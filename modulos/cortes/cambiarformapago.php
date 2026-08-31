<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/qxom385u3mfg3.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Tickets.php");

$t = new Tickets();

$ticket = $t->obtenerTicket(array(
    "idticket" => $_GET["idticket"]
))["ticket"];

// La forma de pago viaja dentro del CFDI, así que mientras haya una factura vigente el
// desglose no se puede mover: cancelada (3) sí, en proceso de cancelación (2) todavía no.
$facturaviva = !empty($ticket["idfactura"]) && $ticket["factura_status"] != 3;

$partidas = $t->obtenerFormasPagoTicket(array("idticket" => $_GET["idticket"]))["partidas"];
$partidas = (is_array($partidas)) ? $partidas : array();

if($facturaviva){
?>
<script>
    $.fancybox.close();
    Swal.fire("Atención", "<?= ($ticket["factura_status"] == 2) ? "Este ticket tiene una factura en proceso de cancelación. Espera a que el SAT la confirme para poder cambiar la forma de pago." : "Este ticket está facturado. Para cambiar la forma de pago primero debes cancelar la factura." ?>", "warning");
</script>
<?
}else if(count($partidas)==0){
?>
<script>
    $.fancybox.close();
    Swal.fire("Atención", "Este ticket no tiene formas de pago registradas.", "warning");
</script>
<?
}else{
    $formaspago = $t->obtenerFormasPagoReasignables()["formaspago"];
    $formaspago = (is_array($formaspago)) ? $formaspago : array();
    $movimientos = $t->obtenerBitacoraFormasPago(array("idticket" => $_GET["idticket"]))["movimientos"];
    $movimientos = (is_array($movimientos)) ? $movimientos : array();

    unset($_SESSION["authToken"]);
    $_SESSION["authToken"]=sha1(uniqid(microtime(), true));
    ?>
    <div id="divCambiarFormaPago" style="width:600px;">
        <div class="row">
            <div class="col-12">
                <h4 class="header-title">Cambiar forma de pago del ticket #<?= $ticket["folio"] ?></h4>
                <small class="text-muted">Los montos no se modifican, únicamente se reasigna la forma de pago de cada partida.</small>
            </div>
        </div>
        <hr>
        <form id="formCambiarFormaPago" name="formCambiarFormaPago">
            <input type="hidden" name="controlador" id="controlador" value="tickets">
            <input type="hidden" name="accion" id="accion" value="cambiarformapago">
            <input type="hidden" name="idticket" id="idticket" value="<?= $_GET["idticket"] ?>">
            <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"] ?>">

            <table class="table table-striped b-t">
                <thead>
                    <tr>
                        <th>Monto</th>
                        <th>Forma de pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?
                    foreach($partidas as $partida){
                        ?>
                        <tr>
                            <td style="width:130px;">
                                $<?= number_format($partida["monto"],2) ?>
                                <? if(!empty($partida["archivo"])){ ?>
                                <br><small class="text-warning">Con comprobante</small>
                                <? } ?>
                            </td>
                            <td>
                                <? if($partida["editable"]){ ?>
                                <select class="form-control" name="formapago[<?= $partida["idformapagoticket"] ?>]" data-original="<?= $partida["idformapago"] ?>" data-archivo="<?= (!empty($partida["archivo"])) ? "1" : "0" ?>">
                                    <?
                                    foreach($formaspago as $formapago){
                                        ?>
                                        <option value="<?= $formapago["idformapago"] ?>" <?= ($formapago["idformapago"]==$partida["idformapago"]) ? "selected" : "" ?>><?= $formapago["nombre"] ?></option>
                                        <?
                                    }
                                    ?>
                                </select>
                                <? if(!empty($partida["archivo"])){ ?>
                                <small class="text-muted d-block mt-1">Si cambias esta partida se eliminará el comprobante <?= htmlspecialchars($partida["archivo"]) ?>.</small>
                                <? } ?>
                                <? }else{ ?>
                                <?= $partida["nombre"] ?>
                                <small class="text-muted d-block">No se puede reasignar<?= ($partida["idformapago"]==Tickets::FORMAPAGO_TARJETAREGALO) ? ": el saldo está ligado a una tarjeta de regalo" : ": el monto está capturado en otra divisa" ?>.</small>
                                <? } ?>
                            </td>
                        </tr>
                        <?
                    }
                    ?>
                </tbody>
            </table>

            <button type="button" onclick="validarFormCambiarFormaPago();" class="btn btn-primary">Guardar cambios</button>
        </form>

        <? if(count($movimientos)>0){ ?>
        <hr>
        <h5 class="header-title">Historial de cambios</h5>
        <table class="table table-sm b-t">
            <tbody>
                <?
                foreach($movimientos as $movimiento){
                    ?>
                    <tr>
                        <td style="width:130px;"><?= date("d/m/Y H:i",strtotime($movimiento["fecha"])) ?></td>
                        <td>
                            <?= $movimiento["formapagoanterior"] ?> $<?= number_format($movimiento["monto"],2) ?> &rarr; <?= $movimiento["formapagonuevo"] ?>
                            <? if(!empty($movimiento["archivoeliminado"])){ ?>
                            <small class="text-muted d-block">Se eliminó el comprobante <?= htmlspecialchars($movimiento["archivoeliminado"]) ?></small>
                            <? } ?>
                        </td>
                        <td><?= $movimiento["vendedor"] ?></td>
                    </tr>
                    <?
                }
                ?>
            </tbody>
        </table>
        <? } ?>
    </div>
    <script>
    function validarFormCambiarFormaPago(){
        var cambios = 0;
        var compctesborrados = 0;

        $("select","#formCambiarFormaPago").each(function(){
            if($(this).val() != $(this).data("original")){
                cambios++;
                if($(this).data("archivo") == 1){
                    compctesborrados++;
                }
            }
        });

        if(cambios == 0){
            Swal.fire("Atención", "No has cambiado ninguna forma de pago.", "warning");
            return;
        }

        var mensaje = "Se reasignarán " + cambios + (cambios == 1 ? " partida." : " partidas.");
        if(compctesborrados > 0){
            mensaje += " Se eliminarán " + compctesborrados + (compctesborrados == 1 ? " comprobante." : " comprobantes.");
        }

        Swal.fire({
            title: "¿Confirmas el cambio?",
            text: mensaje,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, cambiar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if(result.isConfirmed){
                validarFormulario('formCambiarFormaPago');
            }
        });
    }
    </script>
<?
}
?>
