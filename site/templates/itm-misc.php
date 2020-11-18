<?php
	include_once('./itm-prepend.php');
	$itm = $modules->get('ItmMisc');
	$itm->init_configs();

	if ($values->action) {
		$itm->process_input($input);
		$itemID = $values->text('itemID');
		$session->redirect($page->itm_miscURL($itemID));
	}
	/**
	 * Show alert that ITM Item is locked if
	 *  1. ITM Item  isn't new
	 *  2. The ITM Item  has a record lock
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

	$page->headline = "Misc for $itemID";
	$item = $itm->get_item($itemID);
	$hazmat = $item->has_hazmat() ? $item->get_hazmat() : new InvHazmatItem();
	$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
	$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $item]);
	$page->body .= $config->twig->render('items/itm/misc/page.twig', ['page' => $page, 'itm' => $itm, 'item' => $item, 'recordlocker' => $itm->recordlocker]);
	$page->js   .= $config->twig->render('items/itm/misc/js.twig', ['page' => $page, 'itm' => $itm]);
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	if ($session->response_itm) {
		$session->remove('response_itm');
	}

	include __DIR__ . "/basic-page.php";
