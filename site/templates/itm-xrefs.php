<?php
	include_once('./itm-prepend.php');
	$itm_xrefs = $modules->get('ItmXrefs');

	$xrefs = new ProcessWire\WireData();
	$xrefs->cxm  = $modules->get('XrefCxm');
	$xrefs->upcx = $modules->get('XrefUpc');

	if ($values->action) {
		$itm_xrefs->process_input($input);
		$itemID = $values->text('itemID');
		$session->redirect($page->itm_xrefsURL($itemID));
	}

	if ($session->response_xref) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_xref]);
		$session->remove('response_xref');
	}

	$item = $itm->get_item($itemID);
	$page->headline = "ITM: $itemID X-refs";

	/**
	 * Show alert that Item is locked if
	 *  1. Item isn't new
	 *  2. The Item has a record lock
	 *  3. Userid does not match the lock
	 *  Otherwise if not locked, create lock
	 */
	if ($itm->recordlocker->function_locked($itemID) && !$itm->recordlocker->function_locked_by_user($itemID)) {
		$msg = "ITM Item $itemID is being locked by " . $itm->recordlocker->get_locked_user($itemID);
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $itemID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		$page->body .= $html->div('class=mb-3');
	} elseif (!$itm->recordlocker->function_locked($itemID)) {
		$itm->recordlocker->create_lock($itemID);
	}

	$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
	$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $item]);
	$page->body .= $config->twig->render('items/itm/xrefs/page.twig', ['page' => $page, 'itm' => $itm, 'item' => $item, 'xrefs' => $xrefs]);
	$page->js   .= $config->twig->render('items/itm/xrefs/js.twig', ['page' => $page]);


	include __DIR__ . "/basic-page.php";
