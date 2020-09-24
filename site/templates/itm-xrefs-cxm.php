<?php
	$itm = $modules->get('Itm');
	$recordlocker = $modules->get('RecordLockerUser');
	$cxm = $modules->get('XrefCxm');
	$filter_cxm = $modules->get('FilterXrefItemCxm');
	$html = $modules->get('HtmlWriter');
	$page->title = "CXM";

	if ($input->requestMethod('POST') || $input->get->action) {
		$cxm->process_input($input);
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$custitemID = $values->text('action') == 'remove-vxm-item' ? '' : $input->$rm->text('custitemID');

		$session->redirect($page->cxm_itemURL($values->text('custID'), $custitemID));
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
			if ($input->get->custitemID) {
				$custID = $input->get->text('custID');
				$custitemID = $input->get->text('custitemID');
				$page->headline = "ITM: $itemID CXM Item $custitemID for $custID";

				if ($cxm->cxm_item_exists($custID, $custitemID)) {
					$item = $cxm->get_cxm_item($custID, $custitemID);
					$qnotes = $modules->get('QnotesItemCxm');

					/**
					 * Show alert that VXM is locked if
					 *  1. VXM isn't new
					 *  2. The VXM has a record lock
					 *  3. Userid does not match the lock
					 * Otherwise if not locked, create lock
					 */
					if ($recordlocker->function_locked($page->name, $cxm->get_recordlocker_key($item)) && !$recordlocker->function_locked_by_user($page->name, $cxm->get_recordlocker_key($item))) {
						$msg = "VXM ". $cxm->get_recordlocker_key($item) ." is being locked by " . $recordlocker->get_locked_user($page->name, $cxm->get_recordlocker_key($item));
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "VXM ".$cxm->get_recordlocker_key($item)." is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
						$page->body .= $html->div('class=mb-3');
					} elseif (!$recordlocker->function_locked($page->name, $cxm->get_recordlocker_key($item))) {
						$recordlocker->create_lock($page->name, $cxm->get_recordlocker_key($item));
					}
				} else {
					$item = $cxm->get_cxm_item_new();
					$item->setCustid($custID);
					$item->setItemid($itemID);
					$page->headline = "ITM: CXM Creating Item";

					if ($custitemID != 'new') {
						$item->setCustitemid($custitemID);
						$msg = "CXM for Customer $custID cust Item ID $custitemID does not exist";
						$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => 'Error with VXM Record', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $msg]);
						$page->body .= $html->div('class=mb-3');
					}
				}
				$page->searchcustomersURL = $pages->get('pw_template=mci-lookup')->url;
				$page->searchitemsURL     = $pages->get('pw_template=itm-search')->url;
				$page->body .= $config->twig->render('items/cxm/item/form.twig', ['page' => $page, 'item' => $item, 'cxm' => $cxm, 'recordlocker' => $recordlocker]);

				if (!$item->isNew()) {
					$page->body .= $html->div('class=mt-3', $html->h3('', 'Notes'));
					$page->body .= $config->twig->render('items/cxm/item/notes/qnotes.twig', ['page' => $page, 'item' => $item, 'qnotes' => $qnotes]);
					$page->js   .= $config->twig->render('items/cxm/item/notes/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
					$page->js   .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page, 'qnotes' => $qnotes]);
				}
				$page->js   .= $config->twig->render('items/cxm/item/form/js.twig', ['page' => $page, 'cxm' => $cxm, 'item' => $item, 'url_validate' => $pages->get('pw_template=cxm-validate')->httpUrl]);
			} else {
				$recordlocker->remove_lock($page->name);
				$filter_cxm->filter_query($input);
				$filter_cxm->apply_sortby($page);
				$page->headline = "ITEM: CXM Item $itemID";
				$items = $filter_cxm->query->paginate($input->pageNum, 10);

				$page->body .= $config->twig->render('items/itm/xrefs/cxm/list/header.twig', ['page' => $page, 'items' => $items, 'itemid' => $itemID]);
				$page->body .= $config->twig->render('items/itm/xrefs/cxm/list/list.twig', ['page' => $page, 'response' => $session->response_xref, 'items' => $items, 'recordlocker' => $recordlocker]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
				$page->js   .= $config->twig->render('items/itm/xrefs/cxm/list/js.twig', ['page' => $page]);
			}

		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
