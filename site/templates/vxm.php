<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$html = $modules->get('HtmlWriter');
	$vxm = $modules->get('XrefVxm');
	$filter_vxm = $modules->get('FilterXrefItemVxm');

	if ($values->action) {
		$vendorID = $values->text('vendorID');
		$vendoritemID = $values->text('vendoritemID');
		$vxm->process_input($input);

		if ($vxm->vxm_item_exists($vendorID, $vendoritemID)) {
			if ($session->response_xref && $session->response_xref->has_success()) {
				$session->redirect($page->vxm_vendorURL($vendorID));
			}
			$session->redirect($page->vxm_itemURL($vendorID, $vendoritemID));
		} else {
			$session->redirect($page->vxm_vendorURL($vendorID));
		}
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/vxm/bread-crumbs.twig', ['page' => $page]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_xref]);
	}

	if ($values->vendorID) {
		$vendorID = $values->text('vendorID');
		$validate_vendor = $modules->get('LookupVendor');
		$vendor = VendorQuery::create()->findOneById($vendorID);

		if (!$validate_vendor->lookup_vendor($vendorID)) {
			$session->redirect($page->url."?q=$vendorID");
		}

		if ($values->vendoritemID) {
			$vendoritemID = $values->text('vendoritemID');

			if ($vxm->vxm_item_exists($vendorID, $vendoritemID)) {
				$page->headline = "VXM: $vendorID Item $vendoritemID";
				$item = $vxm->get_vxm_item($vendorID, $vendoritemID);

				/**
				 * Show alert that VXM is locked if
				 *  1. VXM isn't new
				 *  2. The VXM has a record lock
				 *  3. Userid does not match the lock
				 * Otherwise if not locked, create lock
				 */
				if ($vxm->recordlocker->function_locked($vxm->get_recordlocker_key($item)) && !$vxm->recordlocker->function_locked_by_user($vxm->get_recordlocker_key($item))) {
					$msg = "VXM ". $vxm->get_recordlocker_key($item) ." is being locked by " . $vxm->recordlocker->get_locked_user($vxm->get_recordlocker_key($item));
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$vxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				} elseif (!$vxm->recordlocker->function_locked($vxm->get_recordlocker_key($item))) {
					$vxm->recordlocker->create_lock($vxm->get_recordlocker_key($item));
				}
			} else {
				$item = $vxm->get_vxm_item_new();
				$item->setVendorid($vendorID);
				$page->headline = "VXM: Creating X-ref for $vendorID";

				if ($vendoritemID != 'new') {
					$item->setVendoritemid($vendoritemID);
					$msg = "VXM for Vendor $vendorID Vendor Item ID $vendoritemID does not exist";
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error with VXM Record', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			}
			$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
			$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
			$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
			$page->body .= $config->twig->render('items/vxm/item/form.twig', ['page' => $page, 'item' => $item, 'vxm' => $vxm]);
			$page->js .= $config->twig->render('items/vxm/item/form/js.twig', ['page' => $page, 'vxm' => $vxm, 'item' => $item, 'url_validate' => $pages->get('pw_template=vxm-validate')->httpUrl]);

			if (!$item->isNew()) {
				$qnotes = $modules->get('QnotesItemVxm');
				$page->body .= $html->hr();
				if ($session->response_qnote) {
					$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
				}
				$page->searchURL = $pages->get('pw_template=msa-noce-ajax')->url;
				$page->body .= $config->twig->render('items/vxm/notes/notes.twig', ['page' => $page, 'qnotes' => $qnotes, 'user' => $user, 'item' => $item]);
				$page->js   .= $config->twig->render('items/vxm/notes/js.twig', ['page' => $page, 'session' => $session]);
				$session->remove('response_qnote');
			}
		} else {
			$vxm->recordlocker->remove_lock($page->name);
			$page->headline = "Vendor X-Ref for $vendor->name";
			$filter_vxm->filter_query($input);
 			$q = $values->q ? $values->text('q') : '';

			if ($values->q) {
				$page->headline = "VXM: Search '$q' for Vendor $vendor->name";
				$filter_vxm->filter_search($values->text('q'));
			}
			$filter_vxm->apply_sortby($page);
			$items = $filter_vxm->query->paginate($input->pageNum, 0);

			$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
			$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
			$page->body .= $config->twig->render('items/vxm/search/item/vendor/form.twig', ['page' => $page, 'q' => $q, 'vendorID' => $vendorID, 'q' => $q]);
			$page->body .= $config->twig->render('items/vxm/list/item/vendor/results.twig', ['page' => $page, 'vxm' => $vxm, 'items' => $items, 'vendorID' => $vendorID]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
			$page->js   .= $config->twig->render('items/vxm/list/item/js.twig', ['page' => $page]);
		}
	} elseif ($values->itemID) {
		$vxm->recordlocker->remove_lock($page->name);
		$itemID = $values->text('itemID');
		$filter_vxm->filter_query($input);
		$filter_vxm->apply_sortby($page);
		$items = $filter_vxm->query->paginate($input->pageNum, 10);

		$page->headline = "VXM: Item $itemID";
		$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
		$page->body .= $html->h3('', $items->getNbResults() ." VXM Items for $itemID");
		$page->body .= $config->twig->render('items/vxm/vxm-links.twig', ['page' => $page]);
		$page->body .= $config->twig->render('items/vxm/list/item/results.twig', ['page' => $page, 'items' => $items, 'recordlocker' => $vxm->recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		$page->js   .= $config->twig->render('items/vxm/list/item/js.twig', ['page' => $page]);
	} else {
		$vxm->recordlocker->remove_lock($page->name);
		$q = $values->q ? strtoupper($values->text('q')) : '';
		$page->title = $values->q ? "VXM: searching vendors for '$q'" : $page->title;
		$filter = $modules->get('FilterVendors');
		$filter->init_query($user);
		$filter->filter_search($q);
		$filter->vendorid($vxm->vendorids());
		$filter->apply_sortby($page);
		$vendors = $filter->query->paginate($input->pageNum, 10);
		$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
		$page->body .= $config->twig->render('items/vxm/search/vendor/search.twig', ['page' => $page, 'vendors' => $vendors]);
		$page->js   .= $config->twig->render('items/vxm/list/item/js.twig', ['page' => $page]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->remove('response_xref');
	include __DIR__ . "/basic-page.php";
