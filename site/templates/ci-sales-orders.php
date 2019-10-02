<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->title = "$custID Sales Orders";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['custid'] != $custID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_customersalesordersURL($custID));
			}
			$session->salesorderstry = 0;
			$module_formatter = $modules->get('CiSalesOrders');
			$module_formatter->init_formatter();
			$document_management = $modules->get('DocumentManagement');

			$refreshurl = $page->get_customersalesordersURL($custID);
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			// $page->body = $config->twig->render('customers/ci/customer/orders-search-form.twig', ['page' => $page, 'input' => $input, 'custid' => $custID]);
			$page->body .= $config->twig->render('customers/ci/customer/sales-orders-list.twig', ['page' => $page, 'custID' => $custID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'document_management' => $document_management]);
			// $page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
		} else {
			if ($session->salesorderstry > 3) {
				$page->headline = $page->title = "Sales Orders File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->salesorderstry++;
				$session->redirect($page->get_customersalesordersURL($custID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
