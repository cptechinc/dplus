<?php
	include_once('./ii-include.php');
	$html = $modules->get('HtmlWriter');

	if ($itemquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('items/ii/bread-crumbs.twig', ['page' => $page, 'item' => $item]);
		$page->title = "$itemID Documents";
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

		$page->body .= $config->twig->render('items/ii/ii-links.twig', ['page' => $page, 'itemID' => $itemID]);

		if ($input->get->folder) {
			$folder = $input->get->text('folder');

			switch ($folder) {
				case 'SO':
					$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));
					$page->title = "Sales Order #$ordn Documents";

					if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
						$documents = $document_management->get_salesorderdocuments($ordn);
					} else {
						$documents = array();
					}

					$href = $pages->get('pw_template=ii-sales-orders')->url."?itemID=$itemID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Item Sales Orders"));
					$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
					break;
				case 'AR':
					$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));
					$date = $input->get->text('date');
					$page->title = "Sales Order #$ordn Documents";

					if (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
						$documents = $document_management->get_saleshistorydocuments($ordn);
					} else {
						$documents = array();
					}

					$href = $pages->get('pw_template=ii-sales-history')->url."?itemID=$itemID&date=$date";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Item Sales History"));
					$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
					break;
				case 'ACT': // Item Activity
					$type = $input->get->text('type');
					$reference = $input->get->text('reference');
					$page->title = "$type $reference Documents";
					$documents = $document_management->get_itemactivitydocuments($type, $reference);

					$href = $pages->get('pw_template=ii-activity')->url."?itemID=$itemID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Item Activity"));
					$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
					break;
				case 'QT':
					$qnbr = $input->get->text('qnbr');
					$page->title = "Quote #$qnbr Documents";
					$documents = $document_management->get_quotedocuments($qnbr);

					$href = $pages->get('pw_template=ii-quotes')->url."?itemID=$itemID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Item Quotes"));
					$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
					break;
				case 'AP':
					$invnbr = PurchaseOrder::get_paddedponumber($input->get->text('invnbr'));
					$page->title = "AP Invoice #$invnbr Documents";
					$documents = $document_management->get_purchasehistorydocuments($invnbr);

					$href = $pages->get('pw_template=ii-purchase-history')->url."?itemID=$itemID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Item Purchase History"));
					$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
					break;
				case 'PO':
					$ponbr = PurchaseOrder::get_paddedponumber($input->get->text('ponbr'));
					$page->title = "Purchase Order #$ponbr Documents";
					$documents = $document_management->get_purchaseorderdocuments($ponbr);

					$href = $pages->get('pw_template=ii-purchase-orders')->url."?itemID=$itemID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Item Purchase Orders"));
					$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
					break;
			}
		} else {
			$documents = $document_management->get_itemdocuments($itemID);
			$page->body .= $config->twig->render('items/ii/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'itemID' => $itemID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
