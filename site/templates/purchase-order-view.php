<?php

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));

		if (ApInvoiceQuery::create()->filterByInvoicenumber($ponbr)->count()) {

		} elseif (PurchaseOrderQuery::create()->filterByPonbr($ponbr)->count()) {

		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order-lookup.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
