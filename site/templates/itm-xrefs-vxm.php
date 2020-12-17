<?php
	include_once('./itm-prepend.php');
	$vxm = $modules->get('XrefVxm');
	$filter_vxm = $modules->get('FilterXrefItemVxm');
	$html = $modules->get('HtmlWriter');
	$page->title = "VXM";

	if ($values->action) {
		$vendorID = $values->text('vendorID');
		$vendoritemID = $values->text('vendoritemID');

		$vxm->process_input($input);

		if ($vxm->xref_exists($vendorID, $vendoritemID, $itemID)) {
			if ($session->response_xref && $session->response_xref->has_success()) {
				$session->redirect($page->vxm_item_exitURL($itemID, $session->response_xref->key), $http301 = false);
			}
			$session->redirect($page->vxm_itemURL($vendorID, $vendoritemID, $itemID), $http301 = false);
		} else {
			$session->redirect($page->vxm_itemidURL($itemID), $http301 = false);
		}
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
	}

	if ($input->get->vendoritemID) {
		$vendorID = $input->get->text('vendorID');
		$vendoritemID = $input->get->text('vendoritemID');
		$page->headline = "ITM: $itemID VXM Item $vendoritemID for $vendorID";

		$item = $vxm->get_create_xref($vendorID, $vendoritemID, $itemID);

		if (!$item->isNew()) {
			/**
			 * Show alert that VXM is locked if
			 *  1. VXM isn't new
			 *  2. The VXM has a record lock
			 *  3. Userid does not match the lock
			 */
			if (!$vxm->lockrecord($item)) {
				$msg = "VXM ". $vxm->get_recordlocker_key($item) ." is being locked by " . $vxm->recordlocker->get_locked_user($vxm->get_recordlocker_key($item));
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$vxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			}
		} else {
			$page->headline = "ITM: VXM Creating Item";
			if ($vendoritemID != 'new') {
				$msg = "VXM for Vendor $vendorID Vendor Item ID $vendoritemID for $itemID does not exist";
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error with VXM Record', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
		$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
		// $page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $itm->get_item($itemID)]);
		$page->body .= $config->twig->render('items/vxm/item/form.twig', ['page' => $page, 'item' => $item, 'vxm' => $vxm]);
		$page->js .= $config->twig->render('items/vxm/item/form/js.twig', ['page' => $page, 'item' => $item, 'url_validate' => $pages->get('pw_template=vxm-validate')->httpUrl]);

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
		$vxm->recordlocker->remove_lock();
		$filter_vxm->filter_input($input);
		$filter_vxm->apply_sortby($page);
		$page->headline = "ITM: VXM Item $itemID";
		$items = $filter_vxm->query->paginate($input->pageNum, $session->display);

		$page->searchvendorsURL = $pages->get('pw_template=vi-search')->url;
		$page->body .= $config->twig->render('items/vxm/list/header.twig', ['page' => $page, 'heading' => $items->getNbResults() ." VXM Items for $itemID"]);
		$page->body .= $config->twig->render('items/vxm/list/item/list.twig', ['page' => $page, 'items' => $items, 'vxm' => $vxm, 'recordlocker' => $vxm->recordlocker]);
		$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		$page->js   .= $config->twig->render('items/vxm/list/item/js.twig', ['page' => $page]);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$session->remove('response_xref');
	include __DIR__ . "/basic-page.php";
