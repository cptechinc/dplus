<?php
	// TODO : INVOICED
	$modules->get('DpagesMpo')->init_purchaseorder_hooks();
	$document_management = $modules->get('DocumentManagement');

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->title = "Purchase Order #$ponbr";
			$purchaseorder = $query->findOne();
			$documents = $document_management->get_purchaseorderdocuments($ponbr);

			$page->body .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/purchase-order.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['page' => $page, 'ponbr' => $ponbr, 'documents' => $documents]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/qnotes.twig', ['page' => $page, 'ponbr' => $ponbr, 'notes' => $purchaseorder->get_notes()]);
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
			$documents = $document_management->get_purchasehistorydocuments($invnbr);
			$page->body .= $config->twig->render('purchase-orders/invoices/invoice/links-header.twig', ['page' => $page, 'invoice' => $invoice, 'document_management' => $document_management]);
			$page->body .= $config->twig->render('purchase-orders/invoices/invoice/invoice.twig', ['page' => $page, 'invoice' => $invoice]);
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
