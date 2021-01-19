<?php
$rm = strtolower($input->requestMethod());
$values = $input->$rm;
$apo = $modules->get('PoAmend');
$html = $modules->get('HtmlWriter');

if ($values->action) {
	$apo->process_input($input);
	$ponbr = PurchaseOrder::get_paddedponumber($values->text('ponbr'));
	$page->fullURL->query->set('ponbr', $ponbr);
	$session->redirect($page->fullURL->getUrl(), $http301 = false);
}

if ($values->ponbr) {
	$ponbr = PurchaseOrder::get_paddedponumber($values->text('ponbr'));

	if ($apo->exists($ponbr)) {
		if ($apo->exists_editable($ponbr) === false) {
			if ($values->load) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "PO # $ponbr can not be loaded for editing"]);
			} else {
				$apo->request_edit_po($ponbr);
				$page->fullURL->query->set('load', 1);
				$session->redirect($page->fullURL->getUrl(), $http301 = false);
			}
		}
		$apo->items->init_configs();
		$page->ponbr = $ponbr;
		$apo->vendorid($ponbr);
		$page->headline = "Editing PO # $ponbr";
		$page->body .= $config->twig->render('warehouse/po-amend/page.twig', ['page' => $page, 'apo' => $apo, 'ponbr' => $ponbr, 'loader' => $config->twig->getLoader()]);

		$page->js  .= $config->twig->render('warehouse/po-amend/po/item/js.twig', ['page' => $page, 'apo' => $apo, 'ponbr' => $ponbr]);
		$page->js  .= $config->twig->render('purchase-orders/purchase-order/edit/lookup/js.twig', ['page' => $page]);

	} else {
		$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Purchase Order not found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "PO # $ponbr not found"]);
		$page->body .= $html->div('class=mb-2');
		$page->body .= $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
	}
} else {
	$apo->delete_editable();
	$page->body .= $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
}

$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
include __DIR__ . "/basic-page.php";
