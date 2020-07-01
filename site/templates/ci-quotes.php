<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
		$page->title = "$custID Quotes";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($module_json->file_exists(session_id(), $page->jsoncode)) {
			if ($json['custid'] != $custID) {
				$module_json->remove_file(session_id(), $page->jsoncode);
				$session->redirect($page->get_customerquotesURL($custID));
			}
			$session->quotestry = 0;

			$refreshurl = $page->get_customerquotesURL($custID);
			$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

			if ($json['error']) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
			} else {
				$module_formatter = $modules->get('SfCiQuotes');
				$module_formatter->init_formatter();
				$docm = $modules->get('DocumentManagementQt');
				$page->body .= $config->twig->render('customers/ci/quotes/quotes.twig', ['page' => $page, 'custID' => $custID, 'json' => $json, 'module_formatter' => $module_formatter, 'blueprint' => $module_formatter->get_tableblueprint(), 'docm' => $docm]);
			}
		} else {
			if ($session->quotestry > 3) {
				$page->headline = $page->title = "Quotes File could not be loaded";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
			} else {
				$session->quotestry++;
				$session->redirect($page->get_customerquotesURL($custID));
			}
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
