<?php
	$itm = $modules->get('Itm');
	$recordlocker = $modules->get('RecordLockerUser');
	$vxm = $modules->get('XrefVxm');
	$filter_vxm = $modules->get('FilterXrefItemVxm');
	$html = $modules->get('HtmlWriter');
	$page->title = "VXM";

	if ($input->requestMethod('POST') || $input->get->action) {
		$vxm->process_input($input);
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$vendoritemID = $values->text('action') == 'remove-vxm-item' ? '' : $input->$rm->text('vendoritemID');

		$session->redirect($page->vxm_itemURL($values->text('vendorID'), $vendoritemID));
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/itm/bread-crumbs.twig', ['page' => $page, 'page_itm' => $page->parent, 'input' => $input]);

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->itemID) {
		$itemID = strtoupper($input->get->text('itemID'));

		if ($itm->item_exists($itemID)) {
			if ($input->get->vendoritemID) {
				$vendorID = $input->get->text('vendorID');
				$vendoritemID = $input->get->text('vendoritemID');
				$page->headline = "ITM: $itemID VXM Item $vendoritemID for $vendorID";

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
				$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $itm->get_item($itemID)]);
				$page->body .= $config->twig->render('items/vxm/item/form.twig', ['page' => $page, 'item' => $item, 'vxm' => $vxm, 'recordlocker' => $recordlocker]);
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
				$recordlocker->remove_lock($page->name);
				$filter_vxm->filter_query($input);
				$filter_vxm->apply_sortby($page);
				$page->headline = "ITEM: VXM Item $itemID";
				$items = $filter_vxm->query->paginate($input->pageNum, 10);

				$page->body .= $config->twig->render('items/vxm/item-list-header.twig', ['page' => $page, 'heading' => $items->getNbResults() ." VXM Items for $itemID"]);
				$page->body .= $config->twig->render('items/vxm/item-list.twig', ['page' => $page, 'items' => $items, 'vxm' => $vxm, 'recordlocker' => $recordlocker]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
			}

		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
