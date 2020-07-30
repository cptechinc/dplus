<?php
	$epo = $modules->get('PurchaseOrderEdit');

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$page->ponbr = $ponbr;
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($epo->exists($ponbr)) {
			$page->headline = "Editing Purchase Order #$ponbr";
			$purchaseorder = $query->findOne();

			if ($epo->exists_editable($ponbr)) {
				$po_edit = $epo->get_editable_header($ponbr);
				$page->body .= $config->twig->render('purchase-orders/purchase-order/edit/edit.twig', ['page' => $page, 'epo' => $epo, 'po' => $po_edit]);
				$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/js.twig', ['page' => $page, 'epo' => $epo]);
			} else {
				if ($input->get->load) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "PO # $ponbr can not be loaded for editing"]);
				} else {
					$epo->request_po_edit($ponbr);
					$page->fullURL->query->set('load', 1);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}
			}
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body .= $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body .= $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
