<?php
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$module_qnotes = $modules->get('QnotesSalesOrder');
	$lookup_orders = $modules->get('LookupSalesOrder');

	if ($input->requestMethod('POST')) {
		$response = $module_qnotes->process_input($input);
		$page->fullURL->query->remove('linenbr');
		$session->redirect($page->fullURL->getURL());
	} else {
		if ($input->get->ordn) {
			$ordn = $input->get->text('ordn');

			if ($lookup_orders->lookup_salesorder($ordn) || $lookup_orders->lookup_saleshistory($ordn)) {
				if ($lookup_orders->lookup_salesorder($ordn)) {
					$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
				} elseif ($lookup_orders->lookup_saleshistory($ordn)) {
					$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
					$module_qnotes = $modules->get('QnotesSalesHistory');
				}
				$page->title = "Sales Order #$ordn Notes";
				if ($session->response_qnote) {
					$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->qnote]);
				}
				$page->body = $config->twig->render('sales-orders/sales-order/qnotes-page.twig', ['page' => $page, 'user' => $user, 'ordn' => $ordn, 'order' => $order, 'qnotes_so' => $module_qnotes]);
				$page->body .= $config->twig->render('sales-orders/sales-order/notes/add-note-modal.twig', ['page' => $page, 'ordn' => $ordn, 'qnotes_so' => $module_qnotes]);
				$config->scripts->append(hash_templatefile('scripts/orders/order-notes.js'));
			} else {
				$page->headline = "Sales Order #$ordn could not be found";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Order Number is correct or if it is in Sales History"]);
			}
		} else {
			$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
		}
	}

	include __DIR__ . "/basic-page.php";
