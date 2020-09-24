<?php
	include_once('./ci-include.php');

	if ($customerquery->count()) {
		$page->show_breadcrumbs = false;
		$page->body .= $config->twig->render('customers/ci/bread-crumbs.twig', ['page' => $page, 'customer' => $customer]);
		$page->title = "$custID Pricing";

		$module_json = $modules->get('JsonDataFiles');
		$json = $module_json->get_file(session_id(), $page->jsoncode);

		if ($input->get->itemID) {
			$itemID = $input->get->text('itemID');
			$page->title .= " for $itemID";

			if ($module_json->file_exists(session_id(), $page->jsoncode)) {
				if ($json['custid'] != $custID) {
					$module_json->remove_file(session_id(), $page->jsoncode);
					$session->redirect($page->get_customerquotesURL($custID));
				}
				$session->pricingtry = 0;

				$refreshurl = $page->get_customerpricingURL($custID, $itemID);
				$page->body .= $config->twig->render('customers/ci/ci-links.twig', ['page' => $page, 'custID' => $custID, 'lastmodified' => $module_json->file_modified(session_id(), $page->jsoncode), 'refreshurl' => $refreshurl]);

				if ($json['error']) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
				} else {
					$page->body .= $config->twig->render('items/ii/pricing/customer-item.twig', ['page' => $page, 'customer' => $customer, 'json' => $json]);
					$page->body .= $config->twig->render('items/ii/pricing/screen.twig', ['page' => $page, 'itemID' => $itemID, 'json' => $json]);
				}
			} else {
				if ($session->pricingtry > 3) {
					$page->headline = $page->title = "Pricing File could not be loaded";
					$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => $module_json->get_error()]);
				} else {
					$session->pricingtry++;
					$session->redirect($page->get_customerpricingURL($custID, $itemID));
				}
			}
		} else {
			$q = $input->get->q ? $input->get->text('q') : '';

			$filter_itm = $modules->get('FilterItemMaster');
			$filter_itm->init_query($user);
			$filter_itm->filter_search($q);
			$query = $filter_itm->get_query();

			if ($query->count() == 1) {
				$item = $query->findOne();
				$session->redirect($page->get_customerpricingURL($custID, $item->itemid));
			} else {
				$items = $query->paginate($input->pageNum, 10);
			}

			$page->searchURL = $page->url;
			$page->body = $config->twig->render('customers/ci/pricing/item-search.twig', ['page' => $page, 'custID' => $custID, 'items' => $items]);
		}
	}

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
