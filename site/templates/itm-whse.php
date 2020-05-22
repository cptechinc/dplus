<?php
	$itm = $modules->get('Itm');
	$itm_warehouse = $modules->get('ItmWarehouse');
	$itm_warehouse->init2();
	$qnotes = $modules->get('QnotesItemWhseOrder');
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->requestMethod('POST') || $input->get->action) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itm_warehouse->process_input($input);
		$itemID = $values->text('itemID');
		$whseID = $values->text('action') == 'remove-itm-whse' ? '' : $values->text('whseID');
		$session->redirect($page->itm_warehouseURL($itemID, $whseID));
	}

	$page->show_breadcrumbs = false;
	$page->body .= $config->twig->render('items/itm/bread-crumbs.twig', ['page' => $page, 'page_itm' => $page->parent, 'input' => $input]);

	if ($session->response_itm) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
		$session->remove('response_itm');
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			$item = $itm->get_item($itemID);
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);

			if ($input->get->whseID) {
				$whseID = $input->get->text('whseID');

				if ($itm_warehouse->itemwarehouse_exists($whseID, $itemID) || $whseID == 'new') {
					$warehouse_lookup = $modules->get('LookupWarehouse');

					if ($whseID == 'new') {
						$item_warehouse = $itm_warehouse->get_new_itemwarehouse();
						$item_warehouse->setItemid($itemID);
						$page->headline = "Adding Warehouse for Item $itemID";
					} else {
						$item_warehouse = $itm_warehouse->get_itemwarehouse($whseID, $itemID);
						$page->headline = "Warehouse $whseID Item $itemID";
					}

					if (!$item_warehouse->isNew()) {
						/**
						 * Show alert that warehouse is locked if
						 *  1. Warehouse isn't new
						 *  2. The warehouse has a record lock
						 *  3. Userid does not match the lock
						 * Otherwise if not locked, create lock
						 */
						if ($recordlocker->function_locked($page->pw_template, "$item_warehouse->itemid-$item_warehouse->warehouseid") && !$recordlocker->function_locked_by_user($page->pw_template, "$item_warehouse->itemid-$item_warehouse->warehouseid")) {
							$msg = "Warehouse $item_warehouse->warehouseid for $item_warehouse->itemid is being locked by " . $recordlocker->get_locked_user($page->pw_template, "$item_warehouse->itemid-$item_warehouse->warehouseid");
							$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Warehouse $item_warehouse->warehouseid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
							$page->body .= $html->div('class=mb-3');
						} elseif (!$recordlocker->function_locked($page->pw_template, "$item_warehouse->itemid-$item_warehouse->warehouseid")) {
							$recordlocker->create_lock($page->pw_template, "$item_warehouse->itemid-$item_warehouse->warehouseid");
						}
					}

					$page->body .= $config->twig->render('items/itm/warehouse/description.twig', ['page' => $page, 'item' => $item]);
					$page->body .= $config->twig->render('items/itm/warehouse/form.twig', ['page' => $page, 'warehouse' => $item_warehouse, 'm_whse' => $itm_warehouse, 'recordlocker' => $recordlocker]);
					$page->body .= $config->twig->render('items/itm/warehouse/bins-modal.twig', ['page' => $page, 'itemID' => $itemID, 'm_whse' => $itm_warehouse]);
					$page->body .= $html->h3('class=mt-3', 'Notes');
					$page->body .= $config->twig->render('items/itm/warehouse/notes/order/list.twig', ['page' => $page, 'item' => $item_warehouse, 'm_notes' => $qnotes]);
					$page->body .= $config->twig->render('items/itm/warehouse/notes/order/modal.twig', ['page' => $page, 'item' => $item_warehouse, 'm_notes' => $qnotes]);
					$page->js   .= $config->twig->render('items/itm/warehouse/notes/order/js.twig', ['page' => $page, 'm_notes' => $qnotes]);

					// Warehouse Item JS
					$jsconfig = array('warehouses' => $itm_warehouse->get_itemwarehouses_js($itemID), 'in_defaultbin' => $itm_warehouse->configs->inventory->default_bin);
					$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);
					$page->js   .= $config->twig->render('items/itm/warehouse/js.twig', ['page' => $page, 'warehouse' => $item_warehouse, 'm_whse' => $itm_warehouse]);
					$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
				} else {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $itemID does not exist in Warehouse $whseID"]);
					$page->body .= $html->div('class=mb-3');
					$page->body .= $html->a('class=btn btn-primary|href='.$page->itm_warehouseURL($itemID), $html->icon('fa fa-undo')." Back to $itemID Warehouses");
				}
			} else {
				$recordlocker->remove_lock($page->lockcode);
				$page->headline = "Warehouses for $itemID";
				$page->body .= $config->twig->render('items/itm/warehouse/description.twig', ['page' => $page, 'item' => $item]);
				$page->body .= $config->twig->render('items/itm/warehouse/list.twig', ['page' => $page, 'itemID' => $itemID, 'warehouses' => $itm_warehouse->get_itemwarehouses($itemID)]);
			}
		}
	} else {
		$session->redirect($page->itmURL(), $http301 = false);
	}

	include __DIR__ . "/basic-page.php";
