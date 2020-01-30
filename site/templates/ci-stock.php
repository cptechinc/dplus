<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
		$page->title = "$custID Stock";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if (1 == 1) {
			$page->headline = $page->title = "Function Not Available";
			$page->body = $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "CI Stock is Not available"]);
		} else {
			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['custid'] != $custID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_customerstockURL($custID));
				}
				$session->stocktry = 0;

				$refreshurl = $page->get_customerstockURL($custID, $itemID);
				$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$page->body .= $config->twig->render('customers/ci/customer/stock.twig', ['page' => $page, 'customer' => $customer, 'json' => $json]);
				}
			} else {
				if ($session->stocktry > 3) {
					$page->headline = $page->title = "Stock File could not be loaded";
					$page->body = $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
					$page->body .= $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->stocktry++;
					$session->redirect($page->get_customerstockURL($custID, $itemID));
				}
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
