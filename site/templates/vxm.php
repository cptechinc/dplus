<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$html = $modules->get('HtmlWriter');
	$vxm = $modules->get('XrefVxm');
	$vxm->init_configs();
	$filter_vxm = $modules->get('FilterXrefItemVxm');

	if ($values->action) {
		$vendorID = $values->text('vendorID');
		$vendoritemID = $values->text('vendoritemID');
		$itemID = $values->text('itemID');
		$vxm->process_input($input);

		if ($vxm->xref_exists($vendorID, $vendoritemID, $itemID)) {
			if ($session->response_xref && $session->response_xref->has_success()) {
				$session->redirect($page->vxm_vendorURL($vendorID, $session->response_xref->key));
			}
			$session->redirect($page->vxm_itemURL($vendorID, $vendoritemID, $itemID));
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
		$filter_vxm->filter_input($input);

		if (!$validate_vendor->lookup_vendor($vendorID)) {
			$session->redirect($page->url."?q=$vendorID", $http301 = false);
		}


		if ($values->vendoritemID) {
			// IF ITM ID key does not exist, and only one record matches redirect to that record.
			if ($values->offsetExists('itemID') == false && $filter_vxm->query->count() == 1) {
				$xref = $filter_vxm->query->findOne();
				$session->redirect($page->vxm_itemURL($xref->vendorid, $xref->vendoritemid, $xref->itemid), $http301 = false);
			}
		}

		if ($values->vendoritemID && ($values->text('vendoritemID') == 'new' || $filter_vxm->query->count() == 1)) {
			$vendoritemID = $values->text('vendoritemID');
			$itemID       = $values->text('itemID');
			$qnotes = $modules->get('QnotesItemVxm');
			$item = $vxm->get_create_xref($vendorID, $vendoritemID, $itemID);
			$vxm->init_field_attributes_config();

			if (!$item->isNew()) {
				$page->headline = "VXM: $vendorID Item $vendoritemID for $itemID";

				/**
				 * Show alert that VXM is locked if
				 *  1. VXM isn't new
				 *  2. The VXM has a record lock
				 *  3. Userid does not match the lock
				 * Otherwise if not locked, create lock
				 */
				if (!$vxm->lockrecord($item)) {
					$msg = "VXM ". $vxm->get_recordlocker_key($item) ." is being locked by " . $vxm->recordlocker->get_locked_user($vxm->get_recordlocker_key($item));
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$vxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			}

			if ($item->isNew()) {
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
			$page->body .= $config->twig->render('items/vxm/item/form/display.twig', ['page' => $page, 'item' => $item, 'vxm' => $vxm, 'qnotes' => $qnotes]);
			$page->js .= $config->twig->render('items/vxm/item/form/js.twig', ['page' => $page, 'vxm' => $vxm, 'item' => $item, 'url_validate' => $pages->get('pw_template=vxm-validate')->httpUrl]);

			if (!$item->isNew()) {
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
			$vxm->recordlocker->remove_lock();
			$page->headline = "Vendor X-Ref for $vendor->name";
			$filter_vxm->filter_input($input);
			$q = $values->q ? $values->text('q') : '';

			if ($values->q) {
				$page->headline = "VXM: Search '$q' for Vendor $vendor->name";
				$filter_vxm->search($values->text('q'));
			}
			$filter_vxm->apply_sortby($page);
			$items = $filter_vxm->query->paginate($input->pageNum, $session->display);

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
		$filter_vxm->filter_input($input);
		$filter_vxm->apply_sortby($page);
		$items = $filter_vxm->query->paginate($input->pageNum, $session->display);

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
		$filter->search($q);
		$filter->vendorid($vxm->vendorids());
		$filter->apply_sortby($page);
		$vendors = $filter->query->paginate($input->pageNum, $session->display);
		$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
		$page->body .= $config->twig->render('items/vxm/search/vendor/search.twig', ['page' => $page, 'vendors' => $vendors]);
		$page->body .= $config->twig->render('items/vxm/new-xref-modal.twig', ['page' => $page]);
		$page->js   .= $config->twig->render('items/vxm/search/vendor/js.twig', ['page' => $page]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
	}

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->remove('response_xref');
	include __DIR__ . "/basic-page.php";
