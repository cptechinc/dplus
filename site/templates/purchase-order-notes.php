<?php
	// TODO : INVOICED
	$modules->get('DpagesMpo')->init_purchaseorder_hooks();

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->show_breadcrumbs = false;
			$page->body .= $config->twig->render('purchase-orders/purchase-order/bread-crumbs.twig', ['page' => $page, 'ponbr' => $ponbr]);
			$purchaseorder = $query->findOne();
			$page->title = "PO #$ponbr Notes";
			$page->body .= $config->twig->render('purchase-orders/purchase-order/qnotes/qnotes.twig', ['page' => $page, 'ponbr' => $ponbr, 'purchaseorder' => $purchaseorder]);
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order-lookup.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
