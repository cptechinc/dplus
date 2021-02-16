<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$config->so = ConfigSalesOrderQuery::create()->findOne();
	$config->po = ConfigPoQuery::create()->findOne();
	$modules->get('DpagesMpo')->init_purchaseorder_hooks();
	$docm = $modules->get('DocumentManagementPo');
	$qnotes = $modules->get('QnotesPo');

	if ($values->action) {
		$ponbr = PurchaseOrder::get_paddedponumber($values->text('ponbr'));
		$qnotes->process_input($input);
		$session->redirect($page->view_purchaseorderURL($ponbr));
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->title = "PO #$ponbr";
			$purchaseorder = $query->findOne();
			$documents = $docm->get_documents_po($ponbr);
			$page->search_notesURL = $pages->get('pw_template=msa-noce-ajax')->url;

			$page->body .= $config->twig->render('purchase-orders/purchase-order/links-header.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/purchase-order.twig', ['page' => $page, 'config' => $config, 'user' => $user, 'purchaseorder' => $purchaseorder, 'qnotes' => $qnotes]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['page' => $page, 'ponbr' => $ponbr, 'documents' => $documents]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/qnotes.twig', ['page' => $page, 'ponbr' => $ponbr, 'purchaseorder' => $purchaseorder, 'qnotes' => $qnotes]);
			$page->body .= $config->twig->render('purchase-orders/purchase-order/invoices.twig', ['page' => $page, 'purchaseorder' => $purchaseorder]);
			$page->js   .= $config->twig->render('purchase-orders/purchase-order/edit/qnotes/js.twig', ['page' => $page]);
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
		$page->body .= $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
		$page->js   .= $config->twig->render('purchase-orders/purchase-order/lookup-form.js.twig');
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	include __DIR__ . "/basic-page.php";
