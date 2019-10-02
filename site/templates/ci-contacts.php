<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->title = ($page->shiptoID) ? $load_customer->get_shipto()->name . " Contacts" : "$customer->name Contacts";
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);


		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);
		$shiptoID = $input->get->shiptoID ? $input->get->text('shiptoID') : '';

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['custid'] != $custID || $json['shipid'] != $shiptoID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_customercontactsURL($custID, $shiptoID));
			}
			$session->cicontacttry = 0;
			$refreshurl = $page->get_customercontactsURL($custID, $shiptoID);
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);
			$page->body .= $config->twig->render('customers/ci/contacts/contacts-screen.twig', ['page' => $page, 'json' => $json, 'module_json' => $module_json, 'date' => $date, 'itemID' => $itemID]);
		} else {
			if ($session->cicontacttry > 3) {
				$page->headline = $page->title = "CI Contact File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->cicontacttry++;
				$session->redirect($page->get_customercontactsURL($custID, $shiptoID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
