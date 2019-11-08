<?php
	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->title = "Purchase Order #$ponbr";
			$purchaseorder = $query->findOne();
			$page->listpage = $pages->get('pw_template=purchase-orders');

			$page->body .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['page' => $page]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/purchase-order-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order-lookup.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
