<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$page->title = "ITM";
	$itm = $modules->get('Itm');
	$itm->init_configs();
	$html = $modules->get('HtmlWriter');
	$exists = false;

	if ($values->action) {
		$itm->process_input($input);

		if ($values->text('action') == 'delete-itm-item') {
			$page->fullURL->query->remove('itemID');
		} else {
			$page->fullURL->query->set('itemID', $values->text('itemID'));
		}
		$page->fullURL->query->remove('action');

		$session->redirect($page->fullURL->getUrl(), $http301 = false);
	}

	if ($session->response_itm) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
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
			if ($itm->recordlocker->function_locked($itemID) && !$itm->recordlocker->function_locked_by_user($itemID)) {
				$msg = "ITM Item $itemID is being locked by " . $itm->recordlocker->get_locked_user($itemID);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $itemID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$itm->recordlocker->function_locked($itemID)) {
				$itm->recordlocker->create_lock($itemID);
			}
		} elseif (strtolower($itemID) == 'new') {
			$exists = true;
			$item = $itm->get_new_item();
			$page->title .= ": New Item";
		}

		if ($exists) {
			$page->customerlookupURL = $pages->get('pw_template=mci-lookup')->url;
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page]);
			$page->body .= $config->twig->render('items/itm/itm-form.twig', ['page' => $page, 'item' => $item, 'itm' => $itm, 'recordlocker' => $itm->recordlocker]);
			$page->js   .= $config->twig->render("items/itm/js.twig", ['page' => $page, 'item' => $item, 'itm' => $itm]);

			if ($itm->item_exists($itemID)) {
				$page->body .= $html->div('class=mb-3', '&nbsp;');
				$page->body .= $html->div('class=mb-3', '&nbsp;');
				$qnotes = $modules->get('QnotesItem');

				$page->body .= $config->twig->render('items/itm/notes/notes.twig', ['page' => $page, 'item' => $item, 'qnotes' => $qnotes, 'user' => $user]);
				$page->js   .= $config->twig->render("items/itm/notes/js.twig", ['page' => $page, 'item' => $item, 'qnotes' => $qnotes, 'session' => $session]);
				$session->remove('qnotes_itm');
			}
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID '$itemID' not found in the Item Master"]);
			$page->body .= $html->div('class=mb-3');
			$page->searchURL = $page->url;
			$page->body .= $html->a("href=$page->url?itemID=new|class=btn btn-secondary mb-2", $html->icon('fa fa-plus') . " Create Item");
			$page->body .= $config->twig->render('items/vxm/search/item/item-search.twig', ['page' => $page, 'items' => array()]);
		}
	} elseif ($input->get->q) {
		$itm->recordlocker->remove_lock();
		$q = strtoupper($input->get->text('q'));

		if ($itm->item_exists($q)) {
			$session->redirect($page->itmURL($q));
		} else {
			$page->headline = "ITM: Searching Items for '$q'";
			$filter = $modules->get('FilterItemMaster');
			$filter->init_query($user);
			$filter->search($q);
			$filter->apply_sortby($page);
			$query = $filter->get_query();
			$items = $query->paginate($input->pageNum, 10);
			$page->searchURL = $page->url;

			$page->body .= $html->a("href=$page->url?itemID=new|class=btn btn-secondary mb-2", $html->icon('fa fa-plus') . " Create Item");
			$page->body .= $config->twig->render('items/vxm/search/item/item-search.twig', ['page' => $page, 'items' => $items]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		}
	} else {
		$itm->recordlocker->remove_lock();
		$page->searchURL = $page->url;
		$filter = $modules->get('FilterItemMaster');
		$filter->init_query($user);
		$filter->apply_sortby($page);
		$query = $filter->get_query();
		$items = $query->paginate($input->pageNum, 10);

		$page->body .= $html->a("href=$page->url?itemID=new|class=btn btn-secondary mb-2", $html->icon('fa fa-plus') . " Create Item");
		$page->body .= $config->twig->render('items/item-search.twig', ['page' => $page, 'items' => $items]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
	}
	include __DIR__ . "/basic-page.php";
