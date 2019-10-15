<?php
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$module_qnotes_crud = $modules->get('QnotesCrud');

	if ($input->requestMethod('POST')) {
		$response = $module_qnotes_crud->process_input_salesorder($input);
		$page->fullURL->query->remove('linenbr');
		$session->redirect($page->fullURL->getURL());

	} else {
		if ($input->get->ordn) {
			$ordn = $input->get->text('ordn');

			if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count() || SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
				if (SalesOrderQuery::create()->filterByOrdernumber($ordn)->count()) {
					$order = SalesOrderQuery::create()->findOneByOrdernumber($ordn);
					$order_items = SalesOrderDetailQuery::create()->findByOrdernumber($ordn);
				} elseif (SalesHistoryQuery::create()->filterByOrdernumber($ordn)->count()) {
					$order = SalesHistoryQuery::create()->findOneByOrdernumber($ordn);
					$order_items = SalesHistoryDetailQuery::create()->filterByOrdernumber($ordn)->find();
				}
				$page->title = "Sales Order #$ordn Notes";
				$page->body = $config->twig->render('sales-orders/sales-order/qnotes-page.twig', ['page' => $page, 'user' => $user, 'ordn' => $ordn, 'order' => $order, 'items' => $order_items]);
				$page->body .= $config->twig->render('sales-orders/sales-order/notes/add-note-modal.twig', ['page' => $page, 'ordn' => $ordn]);
				$config->scripts->append(hash_templatefile('scripts/orders/order-notes.js'));
			} else {
				$page->headline = "Sales Order #$ordn could not be found";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Order Number is correct or if it is in Sales History"]);
			}
		} else {
			$page->body = $config->twig->render('sales-orders/sales-order-lookup.twig', ['page' => $page]);
		}
	}

	include __DIR__ . "/basic-page.php";
