<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);

		$page->title = "$vendor->name Documents";
		$document_management = $modules->get('DocumentManagement');
		$html = $modules->get('HtmlWriter');

		if ($input->get->document && $input->get->folder) {
			$folder = $input->get->text('folder');
			$filename = $input->get->text('document');
			$document_management->move_document($folder, $filename);

			if ($document_management->is_filewebaccessible($filename)) {
				$session->redirect($config->url_webdocs.$filename);
			}
		}

		$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'vendorID' => $vendorID]);

		if ($input->get->folder) {
			$folder = $input->get->text('folder');

			switch ($folder) {
				case 'PO':
					$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
					$page->title = "PO #$ponbr Documents";

					if (PurchaseOrderQuery::create()->filterByPonbr($ponbr)->count()) {
						$documents = $document_management->get_purchaseorderdocuments($ponbr);
					} else {
						$documents = array();
					}

					$href = $pages->get('pw_template=vi-purchase-orders')->url."?vendorID=$vendorID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Vendor Purchase Orders"));
					$page->body .= $config->twig->render('vendors/vi/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'vendorID' => $vendorID]);
					break;
				case 'AP':
					$invnbr = $input->get->text('invnbr');
					$page->title = "AP Invoice #$invnbr Documents";

					if (ApInvoiceQuery::create()->filterByInvoicenumber($invnbr)->count()) {
						$documents = $document_management->get_purchasehistorydocuments($invnbr);
					} else {
						$documents = array();
					}

					$href = $pages->get('pw_template=vi-purchase-history')->url."?vendorID=$vendorID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Vendor Purchase History"));
					$page->body .= $config->twig->render('vendors/vi/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'vendorID' => $vendorID]);
					break;
			}
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'No documents available.']);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
