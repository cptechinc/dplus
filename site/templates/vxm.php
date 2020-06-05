<?php
	$html = $modules->get('HtmlWriter');
	$vxm = $modules->get('XrefVxm');
	$filter_vxm = $modules->get('FilterXrefItemVxm');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$vendorID = $input->$rm->text('vendorID');
		$vendoritemID = $input->$rm->text('vendoritemID');
		$vxm->process_input($input);

		if ($vxm->vxm_item_exists($vendorID, $vendoritemID)) {
			$session->redirect($pages->vxm_itemURL($vendorID, $vendoritemID));

		} else {
			$session->redirect($pages->vxm_vendorURL($vendorID));
		}
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/vxm/bread-crumbs.twig', ['page' => $page]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->vendorID) {
		$vendorID = $input->get->text('vendorID');
		$validate_vendor = $modules->get('LookupVendor');
		$vendor = VendorQuery::create()->findOneById($vendorID);

		if (!$validate_vendor->lookup_vendor($vendorID)) {
			$session->redirect($page->url."?q=$vendorID");
		}

		if ($input->get->vendoritemID) {
			$vendoritemID = $input->get->text('vendoritemID');

			if ($vxm->vxm_item_exists($vendorID, $vendoritemID)) {
				$item = $vxm->get_vxm_item($vendorID, $vendoritemID);

				/**
				 * Show alert that VXM is locked if
				 *  1. VXM isn't new
				 *  2. The VXM has a record lock
				 *  3. Userid does not match the lock
				 * Otherwise if not locked, create lock
				 */
				if ($recordlocker->function_locked($page->name, $vxm->get_recordlocker_key($item)) && !$recordlocker->function_locked_by_user($page->name, $vxm->get_recordlocker_key($item))) {
					$msg = "VXM ". $vxm->get_recordlocker_key($item) ." is being locked by " . $recordlocker->get_locked_user($page->name, $vxm->get_recordlocker_key($item));
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$vxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				} elseif (!$recordlocker->function_locked($page->name, $vxm->get_recordlocker_key($item))) {
					$recordlocker->create_lock($page->name, $vxm->get_recordlocker_key($item));
				}
			} else {
				$item = $vxm->get_vxm_item_new();
				$item->setVendorid($vendorID);
				$item->setOuritemid($itemID);
				$page->headline = "ITM: VXM Creating Item";

				if ($vendoritemID != 'new') {
					$item->setVendoritemid($vendoritemID);
					$msg = "VXM for Vendor $vendorID Vendor Item ID $vendoritemID does not exist";
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error with VXM Record', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			}
			$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
			$page->body .= $config->twig->render('items/vxm/item/form.twig', ['page' => $page, 'item' => $item, 'vxm' => $vxm, 'recordlocker' => $recordlocker]);
			$page->js .= $config->twig->render('items/vxm/item/form/js.twig', ['page' => $page, 'item' => $item, 'url_validate' => $pages->get('pw_template=vxm-validate')->httpUrl]);
		} else {
			$recordlocker->remove_lock($page->name);
			$page->headline = "VXM: Vendor $vendor->name";
			$filter_vxm->filter_query($input);
			$filter_vxm->apply_sortby($page);
			$items = $filter_vxm->query->paginate($input->pageNum, 10);

			$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
			$page->body .= $html->h3('', $items->getNbResults() . " VXM Items for $vendor->name");
			$page->body .= $config->twig->render('items/vxm/item-list.twig', ['page' => $page, 'items' => $items, 'vendorID' => $vendorID, 'recordlocker' => $recordlocker]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		}
	} elseif ($input->get->itemID) {
		$recordlocker->remove_lock($page->name);
		$itemID = $input->get->text('itemID');
		$filter_vxm->filter_query($input);
		$filter_vxm->apply_sortby($page);
		$items = $filter_vxm->query->paginate($input->pageNum, 10);

		$page->headline = "VXM: Item $itemID";
		$page->body .= $html->h3('', $items->getNbResults() ." VXM Items for $itemID");
		$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
		$page->body .= $config->twig->render('items/vxm/item-list.twig', ['page' => $page, 'items' => $items, 'recordlocker' => $recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
	} elseif ($input->get->search) {
		$recordlocker->remove_lock($page->name);
		$q = $input->get->text('q');
		$searchtype = $input->get->text('search');

		if ($searchtype == 'vendors') {
			$exact_query = VendorQuery::create();

			if ($exact_query->filterByVendorid($q)->count() == 1) {
				$session->redirect($page->vxm_vendorURL($q));
			} else {
				$page->headline = "VXM: Searching vendors for '$q'";
				$search_vendors = $modules->get('FilterVendors');
				$search_vendors->init_query($user);
				$search_vendors->filter_search($q);
				$search_vendors->apply_sortby($page);
				$query = $search_vendors->get_query();
				$vendors = $query->paginate($input->pageNum, 10);
				$page->searchURL = $page->url;
				$page->body .= $config->twig->render('items/vxm/search/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
			}
		} elseif ($searchtype == 'items') {
			$exact_query = ItemMasterItemQuery::create();

			if ($exact_query->filterByItemid($q)->count() == 1) {
				$session->redirect($page->vxm_itemidURL($q));
			}  else {
				$page->headline = "VXM: Searching Items for '$q'";
				$search_items = $modules->get('FilterItemMaster');
				$search_items->init_query($user);
				$search_items->filter_search($q);
				$search_items->apply_sortby($page);
				$query = $search_items->get_query();
				$items = $query->paginate($input->pageNum, 10);
				$page->searchURL = $page->url;
				$page->body .= $config->twig->render('items/vxm/search/item/item-search.twig', ['page' => $page, 'items' => $items]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
			}
		}
	} else {
		$page->body .= $config->twig->render('items/vxm/vxm-search.twig', ['page' => $page]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
