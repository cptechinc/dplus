<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
		$page->title = "$custID Purchase Orders";

		if ($input->get->custpo) {
			$custpo = $input->get->text('custpo');
			$module_json = $modules->get('JsonDataFiles');
			$json = $module_json->get_file(session_id(), $page->jsoncode);

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['custid'] != $custID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_customerpurchaseordersURL($custID));
				}
				$session->purchaseorderstry = 0;

				$refreshurl = $page->get_customerpurchaseordersURL($custID, $itemID);
				$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
				$page->body .= $config->twig->render('customers/ci/customer/purchase-orders.twig', ['page' => $page, 'customer' => $customer, 'json' => $json]);
			} else {
				if ($session->purchaseorderstry > 3) {
					$page->headline = $page->title = "Purchase Orders File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->purchaseorderstry++;
					$session->redirect($page->get_customerpurchaseordersURL($custID, $itemID));
				}
			}
		} else {
			$page->formurl = $page->fullURL->getUrl();
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID]);
			$page->body .= $config->twig->render('customers/ci/customer/po-form.twig', ['page' => $page, 'custID' => $custID]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
