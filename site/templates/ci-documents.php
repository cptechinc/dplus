<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->title = "$custID Documents";
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

		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);

		$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);

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

					$href = $pages->get('pw_template=ci-sales-orders')->url."?custID=$custID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Customer Sales Orders"));
					$page->body .= $config->twig->render('customers/ci/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'custID' => $custID]);
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

					$href = $pages->get('pw_template=ci-sales-history')->url."?custID=$custID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Customer Sales History"));
					$page->body .= $config->twig->render('customers/ci/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'custID' => $custID]);
					break;
				case 'QT':
					$qnbr = $input->get->text('qnbr');
					$page->title = "Quote #$qnbr Documents";

					if (QuoteQuery::create()->filterByQuotenumber($qnbr)->count()) {
						$documents = $document_management->get_quotedocuments($qnbr);
					} else {
						$documents = array();
					}

					$href = $pages->get('pw_template=ci-quotes')->url."?custID=$custID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Customer Quotes"));
					$page->body .= $config->twig->render('customers/ci/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'custID' => $custID]);
					break;
				case 'ARINVC': // AR INVOICES
					$invnbr = $input->get->text('invnbr');
					$page->title = "Invoice #$invnbr Documents";
					$documents = $document_management->get_arinvoicedocuments($invnbr);

					$href = $pages->get('pw_template=ci-open-invoices')->url."?custID=$custID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Customer Open Invoices"));
					$page->body .= $config->twig->render('customers/ci/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'custID' => $custID]);
					break;
				case 'PAY': // CI PAYMENTS
					$invnbr = $input->get->text('invnbr');
					$checknbr = $input->get->text('checknbr');
					$page->title = "Payments on Invoice #$invnbr Documents";
					$documents = $document_management->get_arpaymentdocuments($invnbr, $checknbr);
					$href = $pages->get('pw_template=ci-payments')->url."?custID=$custID";
					$page->body .= $html->div('class=mb-3', $html->a("href=$href|class=btn btn-secondary", $html->icon('fa fa-arrow-left') . " Back to Customer Open Invoices"));
					$page->body .= $config->twig->render('customers/ci/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'custID' => $custID]);
					break;
			}
		} else {
			$documents = [];
			$page->body .= $config->twig->render('customers/ci/documents/documents-dm.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'custID' => $custID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
