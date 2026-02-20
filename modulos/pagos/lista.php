<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");
$p = new Pagos();

$pagos = $p->getPagos($_POST);

if($pagos["result"]=="success"){
?>
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th># Pago</th>
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
                <td><?= $pago["idpago"] ?></td>
                <td><?= $pago["cliente"] ?></td>
                <td>$<?= number_format($pago["total"],2) ?></td>
                <td><?= $pago["formapago"] ?></td>
                <td><?= $p->fecha_formateada($pago["fecha"],false) ?></td>
                <td class="text-center">
                    <?php if(!empty($pago["uuid"])): ?>
                        <i class="fa fa-check text-success"></i>
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
                                <a class="dropdown-item" href="#" onclick="verXML_Pago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-file-code-o"></i> Ver XML
                                </a>
                                <a class="dropdown-item" href="#" onclick="descargarPago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-download"></i> Descargar pago
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="cancelarPago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-times"></i> Cancelar
                                </a>
                            <?php else: ?>
                                <a class="dropdown-item" href="#" onclick="timbrarPago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-file-text"></i> Timbrar
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="cancelarPago(<?= $pago['idpago'] ?>)">
                                    <i class="fa fa-times"></i> Cancelar
                                </a>
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