<?php
	include_once('./itm-prepend.php');

	$itm_pricing = $modules->get('ItmPricing');
	$itm_pricing->init_configs();

	if ($values->action) {
		$rm = strtolower($input->requestMethod());
		$itm_pricing->process_input($input);
		$itemID = $input->$rm->text('itemID');
		$session->redirect($page->itm_pricingURL($itemID));
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

	$page->headline = "Pricing for $itemID";
	$itm_pricing = $modules->get('ItmPricing');
	$item = $itm->get_item($itemID);
	$itempricing = $itm_pricing->get_pricing($itemID);

	$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
	$page->body .= $config->twig->render('items/itm/pricing/form.twig', ['page' => $page, 'item' => $item, 'pricingm' => $itm_pricing, 'item_pricing' => $itempricing]);
	$page->js   .= $config->twig->render('items/itm/pricing/js.twig', ['page' => $page, 'item_pricing' => $itempricing]);
	$session->remove('response_itm');

	include __DIR__ . "/basic-page.php";
