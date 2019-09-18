<?php
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$document_management = $modules->get('DocumentManagement');

		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count() || SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
			$page->title = "Sales Order #$ordn Documents";

			if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
				$documents = $document_management->get_salesorderdocuments($ordn);
			} elseif (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
				$documents = $document_management->get_salesorderdocuments($ordn);
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
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
