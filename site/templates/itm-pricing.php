<?php
	$itm = $modules->get('Itm');
	$itm_pricing = $modules->get('ItmPricing');
	$itm_pricing->init_configs();
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			if ($input->requestMethod('POST') || $input->get->action) {
				$rm = strtolower($input->requestMethod());
				$itm_pricing->process_input($input);
				$itemID = $input->$rm->text('itemID');
				$session->redirect($page->itm_pricingURL($itemID));
			}

			if ($session->response_itm) {
				$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
				$session->remove('response_itm');
			}

			/**
			 * Show alert that warehouse is locked if
			 *  1. Warehouse isn't new
			 *  2. The warehouse has a record lock
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

			$page->headline = "Pricing for $itemID";
			$itm_pricing = $modules->get('ItmPricing');
			$item = $itm->get_item($itemID);
			$itempricing = $itm_pricing->get_pricing($itemID);

			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/pricing/form.twig', ['page' => $page, 'item' => $item, 'pricingm' => $itm_pricing, 'item_pricing' => $itempricing]);
			$page->js   .= $config->twig->render('items/itm/pricing/js.twig', ['page' => $page, 'item_pricing' => $itempricing]);
		} else {
			$session->redirect($page->itmURL($itemID));
		}
	} else {
		$session->redirect($page->itmURL());
	}

	include __DIR__ . "/basic-page.php";
