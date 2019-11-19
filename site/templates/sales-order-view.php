<?php
	$config_salesorders = $modules->get('ConfigsSalesOrders');
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');

	if ($input->get->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));

		if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count() || SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
			if ($page->print) {
				$session->redirect($pages->get('pw_template=sales-order-print')->url."?ordn=$ordn");
			}
			$page->title = "Sales Order #$ordn";
			$type = 'order';
			$document_management = $modules->get('DocumentManagement');
			$module_useractions = $modules->get('FilterUserActions');

			if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
				$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
				$page->listpage = $pages->get('pw_template=sales-orders');
				$notes = SalesOrderNotesQuery::create()->filterByOrdernumber($ordn)->filterByLine(0)->find();
				$documents = $document_management->get_salesorderdocuments($ordn);
			} elseif (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
				$type = 'history';
				$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
				$page->listpage = $pages->get('pw_template=sales-history-orders');
				$notes = SalesHistoryNotesQuery::create()->filterByOrdernumber($ordn)->filterByLine(0)->find();
				$documents = $document_management->get_saleshistorydocuments($ordn);
			}

			$query_useractions = $module_useractions->get_actionsquery($input);
			$actions = $query_useractions->filterBySalesorderlink($ordn)->find();
			$page->body =  $config->twig->render("sales-orders/sales-$type/sales-$type-page.twig", ['page' => $page, 'order' => $order, 'user' => $user, 'document_management' => $document_management, 'notes' => $notes]);
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'shipments' => $shipments, 'urlmaker' => $modules->get('DplusURLs')]);
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents, 'document_management' => $document_management, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['page' => $page, 'notes' => $notes, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/user-actions.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'ordn' => $ordn]);

			if ($type == 'order') {
				$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-actions.twig', ['page' => $page, 'order' => $order]);
			}
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
	}

	if ($page->print) {
		$page->show_title = true;

		if (!$page->is_pdf()) {
			$page->show_title = false;
			$pdfmaker = $modules->get('PdfMaker');
			$pdfmaker->set_fileID("order-$order->ordernumber");
			$pdfmaker->set_filetype('order');
			$pdfmaker->set_url($page->get_pdfURL());
			$pdfmaker->generate_pdf();
		} else {
			$page->show_title = false;
		}

		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
