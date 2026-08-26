<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");
$p = new Pagos();

// Cada renglón es un documento por cobrar: una factura del pedido o la parte del pedido que
// aún no se factura. Así el vendedor decide a qué factura va cada peso.
$renglones = $p->getRenglonesPago($_POST);

if($renglones["result"]=="success"){
    $pedidoanterior = null;
?>
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Documento</th>
                <th>Total</th>
                <th>Restante</th>
                <th>Pago</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?
            foreach($renglones["renglones"] as $tmp){
                $nuevopedido = ($pedidoanterior !== $tmp["idpedido"]);
                $pedidoanterior = $tmp["idpedido"];
            ?>
            <tr<?= ($nuevopedido) ? ' style="border-top:2px solid #dee2e6"' : '' ?>>
                <td><?= ($nuevopedido) ? $tmp["idpedido"] : "" ?></td>
                <td>
                    <? if($tmp["idfactura"] > 0){ ?>
                    <i class="fa fa-check text-success"></i>
                    <?= $tmp["documento"] ?>
                    <? if($tmp["ppd"] == 1){ ?>
                    <span class="badge badge-info">PPD</span>
                    <? } ?>
                    <? }else{ ?>
                    <span class="text-muted"><?= $tmp["documento"] ?></span>
                    <? } ?>
                </td>
                <td>$<?= number_format($tmp["totaldocumento"],2) ?></td>
                <td>$<?= number_format($tmp["restante"],2) ?></td>
                <td>
                    <input type="text" name="txtPago[]" class="form-control txtPago" data-idpedido="<?= $tmp["idpedido"] ?>" data-idfactura="<?= $tmp["idfactura"] ?>" data-maximo="<?= $tmp["restante"] ?>" data-restantepedido="<?= $tmp["restantepedido"] ?>" data-requierecomplemento="<?= $tmp["ppd"] ?>">
                </td>
                <td><? echo $p->fecha_formateada($tmp["fecha"],false); ?></td>
            </tr>
            <?
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th class="text-right" colspan="4">Pago recibido</th>
                <td id="totalPago" colspan="2">$0.00</td>
            </tr>
        </tfoot>
    </table>
</div>
<script>
$(document).ready(function(){
    $(".txtPago").on("input", function () {
        // Se sanitiza el valor para que solo sean cantidades monetarias
        let valor = this.value
            .replace(/[^0-9.]/g, '')
            .replace(/(\..*)\./g, '$1');

        // Recuperamos el monto máximo que puede introducirse en este documento
        let maximo = parseFloat($(this).data("maximo"));

        let numero = parseFloat(valor);

        // En caso de que el valor sea mayor, lo ajustamos
        if (!isNaN(maximo) && !isNaN(numero) && numero > maximo) {
            valor = maximo.toFixed(2);
        }

        this.value = valor;

        // Un pedido puede tener varios documentos, pero entre todos no se le puede cobrar más
        // de lo que el pedido debe
        let idpedido = $(this).data("idpedido");
        let restantepedido = parseFloat($(this).data("restantepedido"));
        let otros = 0;

        $(".txtPago").filter(function(){
            return $(this).data("idpedido") == idpedido;
        }).not(this).each(function(){
            otros += parseFloat($(this).val().replace(/,/g, '')) || 0;
        });

        let actual = parseFloat(this.value) || 0;

        if (!isNaN(restantepedido) && (otros + actual) > restantepedido) {
            let permitido = Math.max(0, restantepedido - otros);
            this.value = (permitido > 0) ? permitido.toFixed(2) : "";
        }

        // Calculamos el monto total del pago recibido sumando todos los valores introducidos en los input's
        let total = 0;
        
        $(".txtPago").each(function () {
            let v = $(this).val().replace(/,/g, '');
            total += parseFloat(v) || 0;
        });

        $("#totalPago").html(
            "$" + total.toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        );
    });
});
</script>
<?
}else{
?>
<div class="alert alert-warning mb-0"><?= $renglones["mensaje"] ?></div>
<?
}
?>
