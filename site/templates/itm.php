<?php
	$page->title = "ITM";
	$itm = $modules->get('Itm');
	$itm->init2();
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');
	$exists = false;

	if ($input->requestMethod('POST') || $input->get->action) {
		$itm->process_input($input);
		$page->fullURL->query->remove('action');
		$session->redirect($page->fullURL->getUrl(), $http301 = false);
	}

	if ($session->response_itm) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_itm]);
		$session->remove('response_itm');
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($input->get->itemID) {
		$itemID = strtoupper($input->get->text('itemID'));

		if ($itm->item_exists($itemID)) {
			$exists = true;
			$item = $itm->get_item($itemID);
			$page->title .= ": $itemID";

			/**
			 * Show alert that Item is locked if
			 * NOTE $page->lockcode is defined in Itm.module
			 *  1. Item isn't new
			 *  2. The Item has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->lockcode, $itemID) && !$recordlocker->function_locked_by_user($page->lockcode, $itemID)) {
				$msg = "ITM Item $itemID is being locked by " . $recordlocker->get_locked_user($page->lockcode, $itemID);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $itemID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->lockcode, $itemID)) {
				$recordlocker->create_lock($page->lockcode, $itemID);
			}
		} elseif ($itemID == 'NEW') {
			$exists = true;
			$item = $itm->get_new_item();
			$page->title .= ": New Item";
		}

		if ($exists) {
			$page->customerlookupURL = $pages->get('pw_template=mci-lookup')->url;
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page]);
			$page->body .= $config->twig->render('items/itm/itm-form.twig', ['page' => $page, 'item' => $item, 'm_itm' => $itm, 'recordlocker' => $recordlocker]);
			$page->js   .= $config->twig->render("items/itm/js.twig", ['page' => $page, 'validateurl' => $pages->get('pw_template=itm-json')->url, 'item' => $item, 'm_itm' => $itm]);

			if ($itm->item_exists($itemID)) {
				$page->body .= $html->div('class=mb-3');
				$module_notes = $modules->get('QnotesItem');

				$page->body .= $config->twig->render('items/itm/notes/notes.twig', ['page' => $page, 'item' => $item, 'm_notes' => $module_notes, 'user' => $user, 'db' => $db_dplusdata]);
				$page->body .= $config->twig->render('items/itm/notes/internal/modal.twig', ['page' => $page, 'item' => $item, 'm_notes' => $module_notes, 'user' => $user]);
				$page->body .= $config->twig->render('items/itm/notes/revision/modal.twig', ['page' => $page, 'item' => $item, 'm_notes' => $module_notes, 'user' => $user]);
				$page->body .= $config->twig->render('items/itm/notes/inspection/modal.twig', ['page' => $page, 'item' => $item, 'm_notes' => $module_notes, 'user' => $user]);
				$page->body .= $config->twig->render('items/itm/notes/order/modal.twig', ['page' => $page, 'item' => $item, 'm_notes' => $module_notes]);
				$page->js   .= $config->twig->render("items/itm/notes/js.twig", ['page' => $page, 'validateurl' => $pages->get('pw_template=itm-json')->url, 'item' => $item, 'm_notes' => $module_notes, 'session' => $session]);
				$session->remove('qnotes_itm');
			}
			$page->body .= $config->twig->render("util/ajax-modal.twig");
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID '$itemID' not found in the Item Master"]);
			$page->body .= $html->div('class=mb-3');
			$page->searchURL = $page->url;
			$page->body .= $html->a("href=$page->url?itemID=new|class=btn btn-secondary mb-2", $html->icon('fa fa-plus') . " Create Item");
			$page->body .= $config->twig->render('items/vxm/search/item/item-search.twig', ['page' => $page, 'items' => array()]);
		}
	} elseif ($input->get->q) {
		$recordlocker->remove_lock($page->lockcode);
		$q = strtoupper($input->get->text('q'));
		$exact_query = ItemMasterItemQuery::create();

		if ($exact_query->filterByItemid($q)->count() == 1) {
			$session->redirect($page->itmURL($q));
		} else {
			$page->headline = "ITM: Searching Items for '$q'";
			$search_items = $modules->get('FilterItemMaster');
			$search_items->init_query($user);
			$search_items->filter_search($q);
			$search_items->apply_sortby($page);
			$query = $search_items->get_query();
			$items = $query->paginate($input->pageNum, 10);
			$page->searchURL = $page->url;

			$page->body .= $html->a("href=$page->url?itemID=new|class=btn btn-secondary mb-2", $html->icon('fa fa-plus') . " Create Item");
			$page->body .= $config->twig->render('items/vxm/search/item/item-search.twig', ['page' => $page, 'items' => $items]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		}
	} else {
		$recordlocker->remove_lock($page->lockcode);
		$page->searchURL = $page->url;
		$page->body .= $html->a("href=$page->url?itemID=new|class=btn btn-secondary mb-2", $html->icon('fa fa-plus') . " Create Item");
		$page->body .= $config->twig->render('items/item-search.twig', ['page' => $page, 'items' => array()]);
	}
	include __DIR__ . "/basic-page.php";
