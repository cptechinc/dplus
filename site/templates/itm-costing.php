<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$itm = $modules->get('Itm');
	$itm_costing = $modules->get('ItmCosting');
	$itm_costing->init_configs();
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			if ($values->action) {
				$rm = strtolower($input->requestMethod());
				$itm_costing->process_input($input);
				$itemID = $input->$rm->text('itemID');
				$session->redirect($page->itm_costingURL($itemID));
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

			$page->headline = "Costing for $itemID";
			$item = $itm->get_item($itemID);
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/description.twig', ['page' => $page, 'item' => $item]);
			$page->body .= $config->twig->render('items/itm/costing/page.twig', ['page' => $page, 'item' => $item, 'm_costing' => $itm_costing, 'recordlocker' => $recordlocker]);
		} else {
			$session->redirect($page->itmURL($itemID), $http301 = false);
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
