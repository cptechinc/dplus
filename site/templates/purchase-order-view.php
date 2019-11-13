<?php
	// TODO : INVOICED
	$modules->get('DpagesMpo')->init_purchaseorder_hooks();

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->title = "Purchase Order #$ponbr";
			$purchaseorder = $query->findOne();
			$document_management = $modules->get('DocumentManagement');
			$documents = $document_management->get_purchaseorderdocuments($ponbr);
			$page->listpage = $pages->get('pw_template=purchase-orders');

			$page->body .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['page' => $page]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/purchase-order.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['page' => $page, 'ponbr' => $ponbr, 'documents' => $documents]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/qnotes.twig', ['page' => $page, 'ponbr' => $ponbr, 'notes' => $purchaseorder->get_notes()]);
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order-lookup.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
