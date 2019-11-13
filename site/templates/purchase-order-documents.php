<?php
	// TODO : INVOICED

	if ($input->get->ponbr) {
		$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
		$query = PurchaseOrderQuery::create()->filterByPonbr($ponbr);
		$document_management = $modules->get('DocumentManagement');

		if ($query->count()) {
			$page->title = "Purchase Order #$ponbr Documents";
			$document_management = $modules->get('DocumentManagement');
			$documents = $document_management->get_purchaseorderdocuments($ponbr);
			$page->listpage = $pages->get('pw_template=purchase-orders');

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$document_management->move_document($folder, $filename);

				if ($document_management->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				}
			}
			$page->body .= $config->twig->render('purchase-orders/purchase-order/documents.twig', ['page' => $page, 'ponbr' => $ponbr, 'documents' => $documents]);
		} else {
			$page->headline = $page->title = "Purchase Order #$ponbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Purchase Order Number is correct"]);
		}
	} else {
		$page->title = 'Enter a Purchase Order Number';
		$page->body = $config->twig->render('purchase-orders/purchase-order-lookup.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
