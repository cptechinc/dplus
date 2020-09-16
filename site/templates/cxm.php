<?php
	$html = $modules->get('HtmlWriter');
	$modules->get('DpagesMso')->init_cxm_hooks();
	$cxm = $modules->get('XrefCxm');
	$filter_cxm = $modules->get('FilterXrefItemCxm');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$custID = $input->$rm->text('custID');
		$custitemID = $input->$rm->text('custitemID');
		$cxm->process_input($input);

		if ($cxm->cxm_item_exists($custID, $custitemID)) {
			$session->redirect($page->cxm_itemURL($custID, $custitemID));
		} else {
			$session->redirect($page->cxm_customerURL($custID));
		}
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/cxm/bread-crumbs.twig', ['page' => $page]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/cxm/response.twig', ['response' => $session->response_xref]);
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($input->get->custID) {
		$custID = $input->get->text('custID');
		$validate_customer = $modules->get('LookupCustomer');
		$customer = CustomerQuery::create()->findOneById($custID);

		if (!$validate_customer->lookup_customer($custID)) {
			$session->redirect($page->url."?q=$custID");
		}

		if ($input->get->custitemID) {
			$custitemID = $input->get->text('custitemID');

			if ($cxm->cxm_item_exists($custID, $custitemID)) {
				$item = $cxm->get_cxm_item($custID, $custitemID);
				$page->title = "CXM: $custID Item $custitemID";
				$qnotes = $modules->get('QnotesItemCxm');

				/**
				 * Show alert that CXM is locked if
				 *  1. CXM isn't new
				 *  2. The CXM has a record lock
				 *  3. Userid does not match the lock
				 * Otherwise if not locked, create lock
				 */
				if ($recordlocker->function_locked($page->name, $cxm->get_recordlocker_key($item)) && !$recordlocker->function_locked_by_user($page->name, $cxm->get_recordlocker_key($item))) {
					$msg = "CXM ". $cxm->get_recordlocker_key($item) ." is being locked by " . $recordlocker->get_locked_user($page->name, $cxm->get_recordlocker_key($item));
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "CXM ".$cxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				} elseif (!$recordlocker->function_locked($page->name, $cxm->get_recordlocker_key($item))) {
					$recordlocker->create_lock($page->name, $cxm->get_recordlocker_key($item));
				}
			} else {
				$recordlocker->remove_lock($page->name);
				$item = $cxm->get_cxm_item_new();
				$item->setCustid($custID);
				$page->headline = "ITM: CXM Creating Item";

				if ($custitemID != 'new') {
					$item->setCustomeritemid($custitemID);
					$msg = "CXM for Customer $custID Customer Item ID $custitemID does not exist";
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error with CXM Record', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			}
			$page->searchcustomersURL = $pages->get('pw_template=mci-lookup')->url;
			$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
			$page->body .= $config->twig->render('items/cxm/item/form.twig', ['page' => $page, 'item' => $item, 'cxm' => $cxm, 'recordlocker' => $recordlocker, 'qnotes' => $qnotes]);

			if (!$item->isNew()) {
				$page->body .= $html->div('class=mt-3', $html->h3('', 'Notes'));
				$page->body .= $config->twig->render('items/cxm/item/notes/qnotes.twig', ['page' => $page, 'item' => $item, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('items/cxm/item/notes/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
				$page->js   .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
			}

			$page->js   .= $config->twig->render('items/cxm/item/form/js.twig', ['page' => $page, 'item' => $item, 'url_validate' => $pages->get('pw_template=cxm-validate')->httpUrl]);
		} else {
			$page->headline = "CXM: Customer $customer->name";
			$filter_cxm->filter_query($input);
			$filter_cxm->apply_sortby($page);
			$items = $filter_cxm->query->paginate($input->pageNum, 10);

			$page->body .= $config->twig->render('items/cxm/cxm-links.twig', ['page' => $page]);
			$page->body .= $html->h3('', $items->getNbResults() . " CXM Items for $customer->name");
			$page->body .= $config->twig->render('items/cxm/item-list.twig', ['page' => $page, 'items' => $items, 'custID' => $custID, 'recordlocker' => $recordlocker]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		}
	} elseif ($input->get->itemID) {
		$recordlocker->remove_lock($page->name);
		$itemID = $input->get->text('itemID');
		$filter_cxm->filter_query($input);
		$filter_cxm->apply_sortby($page);
		$items = $filter_cxm->query->paginate($input->pageNum, 10);

		$page->headline = "CXM: Item $itemID";
		$page->body .= $html->h3('', $items->getNbResults() ." CXM Items for $itemID");
		$page->body .= $config->twig->render('items/cxm/cxm-links.twig', ['page' => $page]);
		$page->body .= $config->twig->render('items/cxm/item-list.twig', ['page' => $page, 'items' => $items, 'recordlocker' => $recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
	} elseif ($input->get->search) {
		$recordlocker->remove_lock($page->name);
		$q = strtoupper($input->get->text('q'));
		$searchtype = $input->get->text('search');

		if ($searchtype == 'customers') {
			$validate_customers = $modules->get('LookupCustomer');

			if ($validate_customers->lookup_customer($q)) {
				$session->redirect($page->cxm_customerURL($q));
			} else {
				$page->headline = "CXM: Searching customers for '$q'";
				$search_customers = $modules->get('FilterCustomers');
				$search_customers->init_query($user);
				$search_customers->filter_search($q);
				$search_customers->apply_sortby($page);
				$query = $search_customers->get_query();
				$customers = $query->paginate($input->pageNum, 10);
				$page->searchURL = $page->url;
				$page->body .= $config->twig->render('items/cxm/search/customer/results.twig', ['page' => $page, 'customers' => $customers]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
			}
		} elseif ($searchtype == 'items') {
			$exact_query = ItemMasterItemQuery::create();

			if ($exact_query->filterByItemid($q)->count() == 1) {
				$session->redirect($page->cxm_itemIDURL($q));
			}  else {
				$page->headline = "CXM: Searching Items for '$q'";
				$search_items = $modules->get('FilterItemMaster');
				$search_items->init_query($user);
				$search_items->filter_search($q);
				$search_items->apply_sortby($page);
				$query = $search_items->get_query();
				$items = $query->paginate($input->pageNum, 10);
				$page->searchURL = $page->url;
				$page->body .= $config->twig->render('items/cxm/search/item/item-search.twig', ['page' => $page, 'items' => $items]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
			}
		}
	} else {
		$recordlocker->remove_lock($page->name);
		$page->body .= $config->twig->render('items/cxm/cxm-search.twig', ['page' => $page]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
