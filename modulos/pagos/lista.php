<?
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");
$p = new Pagos();

$pagos = $p->getPagos($_POST);

if($pagos["result"]=="success"){

}else{
?>
<div class="alert alert-warning mb-0"><?= $pagos["mensaje"] ?></div>
<?
}
?>