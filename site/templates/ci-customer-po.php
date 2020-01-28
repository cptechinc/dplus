<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
		$page->title = "$customer->name Purchase Orders";

		if ($input->get->custpo) {
			$custpo = $input->get->text('custpo');
			$module_json = $modules->get('JsonDataFiles');
			$document_management = $modules->get('DocumentManagement');
			$page->title = "$customer->name Purchase Orders that match '$custpo'";

			$refreshurl = $page->get_customerpurchaseordersURL($custID, $custpo);
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

			$page->body .= $html->h2('class=border-bottom border-primary', 'Sales Orders');
			$json = $module_json->get_file(session_id(), 'ci-sales-orders');

			if ($module_json->file_exists(session_id(), 'ci-sales-orders')) {
				if ($json['custid'] != $custID) {
					$module_json->remove_file(session_id(), 'ci-sales-orders');
					$session->redirect($page->get_customerpurchaseordersURL($custID, $custpo));
				}
				$session->salesorderstry = 0;

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$module_formatter = $modules->get('CiSalesOrders');
					$module_formatter->init_formatter();
					$page->body .= $config->twig->render('customers/ci/sales-orders/sales-orders.twig', ['page' => $page, 'custID' => $custID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
				}
			} else {
				if ($session->salesorderstry > 3) {
					$page->headline = $page->title = "Sales Orders File could not be loaded";
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->salesorderstry++;
					$session->redirect($page->get_customerpurchaseordersURL($custID, $custpo));
				}
			}

			$page->body .= $html->div('class=mb-3');
			$page->body .= $html->h2('class=border-bottom border-primary', 'Sales History');
			$json = $module_json->get_file(session_id(), 'ci-sales-history');

			if ($module_json->file_exists(session_id(), 'ci-sales-history')) {
				if ($json['custid'] != $custID) {
					$module_json->remove_file(session_id(), 'ci-sales-history');
					$session->redirect($page->get_customerpurchaseordersURL($custID, $custpo));
				}
				$session->saleshistorytry = 0;
				$module_formatter = $modules->get('SfCiSalesHistory');
				$module_formatter->init_formatter();
				$page->body .= $config->twig->render('customers/ci/sales-history/sales-history.twig', ['page' => $page, 'custID' => $custID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
			} else {
				if ($session->saleshistorytry > 3) {
					$page->headline = $page->title = "Sales History File could not be loaded";
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->saleshistorytry++;
					$session->redirect($page->get_customerpurchaseordersURL($custID, $custpo));
				}
			}
		} else {
			$page->formurl = $page->get_redirURL();
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
			$page->body .= $config->twig->render('customers/ci/purchase-orders/po-form.twig', ['page' => $page, 'custID' => $custID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
