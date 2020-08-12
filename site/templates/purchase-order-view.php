<?php
	$config->so = ConfigSalesOrderQuery::create()->findOne();
	$modules->get('DpagesMpo')->init_purchaseorder_hooks();
	$docm = $modules->get('DocumentManagementPo');

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->title = "Purchase Order #$ponbr";
			$purchaseorder = $query->findOne();
			$documents = $docm->get_documents_po($ponbr);
			$qnotes = $modules->get('QnotesPo');

			$page->body .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/purchase-order.twig', ['page' => $page, 'config' => $config, 'user' => $user, 'purchaseorder' => $purchaseorder]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['page' => $page, 'ponbr' => $ponbr, 'documents' => $documents]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/qnotes.twig', ['page' => $page, 'ponbr' => $ponbr, 'qnotes' => $qnotes]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/invoices.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} elseif ($input->get->invnbr) {
		$invnbr = $input->get->text('invnbr');
		$query = ApInvoiceQuery::create()->filterByInvoicenumber($invnbr);

		if ($query->count()) {
			$page->title = "AP Invoice #$invnbr";
			$invoice = $query->findOne();
			$documents = $docm->get_documents_invoice($invnbr);
			$page->body .= $config->twig->render('purchase-orders/invoices/invoice/links-header.twig', ['page' => $page, 'invoice' => $invoice, 'docm' => $docm]);
			$page->body .= $config->twig->render('purchase-orders/invoices/invoice/invoice.twig', ['page' => $page, 'config' => $config, 'invoice' => $invoice]);
			$page->body .= $config->twig->render('purchase-orders/invoices/invoice/documents.twig', ['page' => $page, 'invnbr' => $invnbr, 'documents' => $documents]);
		} else {
			$page->headline = $page->title = "AP Invoice #$invnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the AP Invoice Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
