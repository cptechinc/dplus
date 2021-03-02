<?php
	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');
		$docm = $modules->get('DocumentManagementSo');
		$lookup_orders = new Dplus\CodeValidators\Mso();

		if ($lookup_orders->order($ordn) || $lookup_orders->invoice($ordn)) {
			$page->title = "Sales Order #$ordn Documents";

			if ($lookup_orders->order($ordn)) {
				$documents = $docm->get_documents($ordn);
			} elseif ($lookup_orders->invoice($ordn)) {
				$documents = $docm->get_documents($ordn);
			}

			if ($input->get->document && $input->get->folder) {
				$folder = $input->get->text('folder');
				$filename = $input->get->text('document');
				$docm->move_document($folder, $filename);

				if ($docm->is_filewebaccessible($filename)) {
					$session->redirect($config->url_webdocs.$filename);
				}
			}
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents]);

		} else {
			$page->headline = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Order Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
