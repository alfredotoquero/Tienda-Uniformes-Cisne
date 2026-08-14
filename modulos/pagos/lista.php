<?
include_once($_SERVER["DOCUMENT_ROOT"]."/vm39845um223u/c91ktn24g7if5u.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");
$p = new Pagos();

$pagos = $p->getPagos($_POST);

if($pagos["result"]=="success"){
?>
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Total</th>
                <th>Forma de Pago</th>
                <th>Fecha</th>
                <th class="text-center">Timbrado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?
            foreach($pagos["pagos"] as $pago){
            ?>
            <tr>
                <td><?= $pago["cliente"] ?></td>
                <td>$<?= number_format($pago["total"],2) ?></td>
                <td><?= $pago["formapago"] ?></td>
                <td><?= $p->fecha_formateada($pago["fecha"],false) ?></td>
                <td class="text-center">
                    <?php if(!empty($pago["uuid"])): ?>
                        <i class="fa fa-check text-success" title="Complemento timbrado"></i>
                    <?php elseif($pago["tiene_factura"] && $pago["status"] == 1): ?>
                        <i class="fa fa-clock-o text-warning" title="Complemento de pago pendiente de timbrar"></i>
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?php if(!empty($pago["uuid"])): ?>
                                <a class="dropdown-item" href="#" onclick="verPDF_Pago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-file-pdf-o"></i> Ver PDF
                                </a>
                                <a class="dropdown-item" href="#" onclick="descargarXML_Pago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-file-code-o"></i> Descargar XML
                                </a>
                                <a class="dropdown-item" href="#" onclick="descargarPago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-download"></i> Descargar pago
                                </a>
                                <?php if($pago["status"] == 1): ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" data-fancybox data-type="ajax" data-src="/modulos/pagos/cancelar.php?idpago=<?= $pago['idpago'] ?>">
                                        <i class="fa fa-times"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if($pago["tiene_factura"] && $pago["status"] == 1): ?>
                                    <a class="dropdown-item" href="#" onclick="timbrarPago(<?= $pago['idpago'] ?>)">
                                        <i class="fa fa-file-text"></i> Timbrar
                                    </a>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>
                                <?php if($pago["status"] == 1): ?>
                                    <a class="dropdown-item text-danger" href="#" onclick="cancelarPago(<?= $pago['idpago'] ?>)">
                                        <i class="fa fa-times"></i> Cancelar
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?
            }
            ?>
        </tbody>
    </table>
</div>
<?
}else{
?>
<div class="alert alert-warning mb-0"><?= $pagos["mensaje"] ?></div>
<?
}
?>