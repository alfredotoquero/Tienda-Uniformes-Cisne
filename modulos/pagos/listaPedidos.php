<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");
$p = new Pagos();

$pedidos = $p->getPedidosCliente($_POST);

if($pedidos["result"]=="success"){
?>
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Total</th>
                <th>Abonado</th>
                <th>Restante</th>
                <th>Pago</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?
            foreach($pedidos["pedidos"] as $tmp){
            ?>
            <tr>
                <td><?= $tmp["idpedido"] ?></td>
                <td>$<?= number_format($tmp["total"],2) ?></td>
                <td>$<?= number_format($tmp["abonado"],2) ?></td>
                <td>$<? echo number_format($tmp["total"] - $tmp["abonado"], 2); ?></td>
                <td>
                    <input type="text" name="txtPago[<?= $tmp["idpedido"] ?>]" class="form-control txtPago" data-maximo="<?= $tmp["total"] - $tmp["abonado"] ?>">
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

        // Recuperamos el monto máximo que puede introducirse
        let maximo = parseFloat($(this).data("maximo"));

        let numero = parseFloat(valor);

        // En caso de que el valor sea mayor, lo ajustamos
        if (!isNaN(maximo) && !isNaN(numero) && numero > maximo) {
            valor = maximo.toString();
        }

        this.value = valor;

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
<div class="alert alert-warning mb-0"><?= $pedidos["mensaje"] ?></div>
<?
}
?>