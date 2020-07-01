<?php
	include_once('./vi-include.php');

	if ($vendorquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('vendors/vi/bread-crumbs.twig', ['page' => $page, 'vendor' => $vendor]);

		$page->title = "$vendor->name Documents";
		$docm = $modules->get('DocumentManagementVi');
		$html = $modules->get('HtmlWriter');

		if ($input->get->document && $input->get->folder) {
			$folder = $input->get->text('folder');
			$filename = $input->get->text('document');
			$docm->move_document($folder, $filename);

			if ($docm->is_filewebaccessible($filename)) {
				$session->redirect($config->url_webdocs.$filename);
			}
		}

		$page->body .= $config->twig->render('vendors/vi/vi-links.twig', ['page' => $page, 'vendorID' => $vendorID]);

		if ($input->get->folder) {
			$folder = $input->get->text('folder');

			switch ($folder) {
				case 'PO':
					$docm = $modules->get('DocumentManagementPo');
					$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
					$page->title = "PO #$ponbr Documents";
					$documents = $docm->get_documents_po($ponbr);

					$href = $pages->get('pw_template=vi-purchase-orders')->url."?vendorID=$vendorID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Vendor Purchase Orders"));
					$page->body .= $config->twig->render('vendors/vi/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'docm' => $docm, 'vendorID' => $vendorID]);
					break;
				case 'AP':
					$docm = $modules->get('DocumentManagementPo');
					$invnbr = $input->get->text('invnbr');
					$page->title = "AP Invoice #$invnbr Documents";
					$documents = $docm->get_documents_invoice($invnbr);

					$href = $pages->get('pw_template=vi-purchase-history')->url."?vendorID=$vendorID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Vendor Purchase History"));
					$page->body .= $config->twig->render('vendors/vi/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'docm' => $docm, 'vendorID' => $vendorID]);
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
