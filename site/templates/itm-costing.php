<?php
	include_once('./itm-prepend.php');
	$itm_costing = $modules->get('ItmCosting');
	$itm_costing->init_configs();

	if ($values->action) {
		$rm = strtolower($input->requestMethod());
		$itm_costing->process_input($input);
		$itemID = $input->$rm->text('itemID');
		$session->redirect($page->itm_costingURL($itemID));
	}

	/**
	 * Show alert that Item is locked
	 */
	if ($itm->recordlocker->function_locked($itemID) && !$itm->recordlocker->function_locked_by_user($itemID)) {
		$msg = "ITM Item $itemID is being locked by " . $itm->recordlocker->get_locked_user($itemID);
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $itemID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		$page->body .= $html->div('class=mb-3');
	} elseif (!$itm->recordlocker->function_locked($itemID)) {
		$itm->recordlocker->create_lock($itemID);
	}

	$page->headline = "Costing for $itemID";
	$item = $itm->get_item($itemID);
	$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
	$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $item]);
	$page->body .= $config->twig->render('items/itm/costing/page.twig', ['page' => $page, 'itm' => $itm, 'item' => $item, 'm_costing' => $itm_costing, 'recordlocker' => $itm->recordlocker]);
	$session->remove('response_itm');

	include __DIR__ . "/basic-page.php";
