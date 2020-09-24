<?php
	$html = $modules->get('HtmlWriter');
	$docm = $modules->get('DocumentManagementPo');

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);

		if ($query->count()) {
			$page->title = "Purchase Order #$ponbr Documents";

			$documents = $docm->get_documents_po($ponbr);
			$page->listpage = $pages->get('pw_template=purchase-orders');

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$docm->move_document($folder, $filename);

				if ($docm->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				}
			}
			$page->body .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['page' => $page, 'ponbr' => $ponbr, 'documents' => $documents]);
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} elseif ($input->get->invnbr) {
		$invnbr = $input->get->text('invnbr');
		$query = ApInvoiceQuery::create()->filterByInvoicenumber($invnbr);

		if ($query->count()) {
			$page->title = "AP Invoice #$invnbr Documents";
			$documents = $docm->get_documents_invoice($invnbr);

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$docm->move_document($folder, $filename);

				if ($docm->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				} else {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Document Storage Error", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$filename could not be found"]);
					$page->body .= $html->div('class=mb-3');
				}
			}
			$page->body .= $config->twig->render('purchase-orders/invoices/invoice/documents.twig', ['page' => $page, 'invnbr' => $invnbr, 'documents' => $documents]);
		} else {
			$page->headline = $page->title = "AP Invoice #$invnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the AP Invoice Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order/lookup-form.twig', ['page' => $page]);
	}

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
