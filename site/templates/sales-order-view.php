<?php
	$config->so = ConfigSalesOrderQuery::create()->findOne();
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');
	$lookup_orders = $modules->get('LookupSalesOrder');

	if ($input->get->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));

		if ($lookup_orders->lookup_salesorder($ordn) || $lookup_orders->lookup_saleshistory($ordn)) {
			if ($page->print) {
				$session->redirect($pages->get('pw_template=sales-order-print')->url."?ordn=$ordn");
			}

			$page->title = "Sales Order #$ordn";

			$docm = $modules->get('DocumentManagementSo');
			$module_useractions = $modules->get('FilterUserActions');
			$lookup_orders = $modules->get('LookupSalesOrder');

			if ($lookup_orders->lookup_salesorder($ordn)) {
				$type = 'order';
				$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
				$page->listpage = $pages->get('pw_template=sales-orders');
				$documents = $docm->get_documents($ordn);
				$module_qnotes = $modules->get('QnotesSalesOrder');
			} elseif ($lookup_orders->lookup_saleshistory($ordn)) {
				$type = 'history';
				$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
				$page->listpage = $pages->get('pw_template=sales-history-orders');
				$documents = $docm->get_documents($ordn);
				$module_qnotes = $modules->get('QnotesSalesHistory');
			}

			$query_useractions = $module_useractions->get_actionsquery($input);
			$actions = $query_useractions->filterBySalesorderlink($ordn)->find();
			$page->body =  $config->twig->render("sales-orders/sales-$type/sales-$type-page.twig", ['page' => $page, 'config' => $config->so, 'order' => $order, 'user' => $user, 'docm' => $docm, 'company' => $config->company, 'loader' => $config->twig->getLoader()]);
			$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['page' => $page, 'order' => $order, 'urlmaker' => $modules->get('DplusURLs')]);
			$page->body .= $config->twig->render('sales-orders/sales-order/documents.twig', ['page' => $page, 'documents' => $documents, 'docm' => $docm, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['page' => $page, 'qnotes_so' => $module_qnotes, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/user-actions.twig', ['page' => $page, 'module_useractions' => $module_useractions, 'actions' => $actions, 'ordn' => $ordn]);

			if ($type == 'order') {
				$page->body .= $config->twig->render('sales-orders/sales-order/sales-order-actions.twig', ['page' => $page, 'order' => $order]);
			}
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => "Order # $ordn not found", 'msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
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
