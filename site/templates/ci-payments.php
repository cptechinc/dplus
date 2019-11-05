<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
		$page->title = "$custID Payments";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['custid'] != $custID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_customerpaymentsURL($custID));
			}
			$session->paymentstry = 0;
			$module_formatter = $modules->get('CiPayments');
			$module_formatter->init_formatter();
			$refreshurl = $page->get_customerpaymentsURL($custID);
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('customers/ci/payments/payments.twig', ['page' => $page, 'custID' => $custID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint()]);
		} else {
			if ($session->paymentstry > 3) {
				$page->headline = $page->title = "Payments File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->paymentstry++;
				$session->redirect($page->get_customerpaymentsURL($custID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
