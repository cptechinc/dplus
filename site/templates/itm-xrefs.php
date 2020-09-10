<?php
	$itm = $modules->get('Itm');
	$itm_xrefs = $modules->get('ItmXrefs');

	$xrefs = new ProcessWire\WireData();
	$xrefs->cxm  = $modules->get('XrefCxm');
	$xrefs->upcx = $modules->get('XrefUpc');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$itm_xrefs->process_input($input);
		$itemID = $input->$rm->text('itemID');
		$session->redirect($page->itm_xrefsURL($itemID));
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/itm/bread-crumbs.twig', ['page' => $page, 'page_itm' => $page->parent, 'input' => $input]);

	if ($session->response_itm) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
		$session->remove('response_itm');
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			$item = $itm->get_item($itemID);
			$page->headline = "ITM: $itemID X-refs";

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
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $item]);
			$page->body .= $config->twig->render('items/itm/xrefs/page.twig', ['page' => $page, 'recordlocker' => $recordlocker, 'm_itm' => $itm, 'item' => $item, 'xrefs' => $xrefs]);
			$page->js   .= $config->twig->render('items/itm/xrefs/js.twig', ['page' => $page]);
		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
