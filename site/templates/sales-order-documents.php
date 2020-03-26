<?php
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$document_management = $modules->get('DocumentManagement');
		$lookup_orders = $modules->get('LookupSalesOrder');

		if ($lookup_orders->lookup_salesorder($ordn) || $lookup_orders->lookup_saleshistory($ordn)) {
			$page->title = "Sales Order #$ordn Documents";

			if ($lookup_orders->lookup_salesorder($ordn)) {
				$documents = $document_management->get_salesorderdocuments($ordn);
			} elseif ($lookup_orders->lookup_saleshistory($ordn)) {
				$documents = $document_management->get_saleshistorydocuments($ordn);
			}

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$document_management->move_document($folder, $filename);

				if ($document_management->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				}
			}
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management]);

		} else {
			$page->headline = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
