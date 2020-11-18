<?php
	include_once('./itm-prepend.php');
	$itmw = $modules->get('ItmWarehouse');
	$itmw->init2();
	$qnotes = $modules->get('QnotesItemWhseOrder');
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($values->action) {
		$itmw->process_input($input);
		$itemID = $values->text('itemID');
		$whseID = $values->text('action') == 'remove-itm-whse' ? '' : $values->text('whseID');
		$session->redirect($page->itm_warehouseURL($itemID, $whseID));
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	$item = $itm->get_item($itemID);

	if ($input->get->whseID) {
		$whseID = $input->get->text('whseID');
		$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);

		if ($itmw->itemwarehouse_exists($itemID, $whseID) || $whseID == 'new') {
			$warehouse_lookup = $modules->get('LookupWarehouse');
			if ($whseID == 'new') {
				$page->headline = "Adding Warehouse for Item $itemID";
				$whseID = '';
			} else {
				$page->headline = "Warehouse $whseID Item $itemID";
			}
			$item_warehouse = $itmw->get_create_itemwarehouse($itemID, $whseID);

			if (!$item_warehouse->isNew()) {
				$itmw->lockrecord($item_warehouse);

				if ($itmw->recordlocker->function_locked($itmw->get_warehouseitem_lockkey($item_warehouse)) && !$itmw->recordlocker->function_locked_by_user($itmw->get_warehouseitem_lockkey($item_warehouse))) {
					$msg = "Warehouse $item_warehouse->warehouseid for $item_warehouse->itemid is being locked by " . $itmw->recordlocker->get_locked_user($itmw->get_warehouseitem_lockkey($item_warehouse));
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Warehouse $item_warehouse->warehouseid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
					$page->body .= $html->div('class=mb-3');
				}
			}

			$page->body .= $config->twig->render('items/itm/warehouse/description.twig', ['page' => $page, 'item' => $item]);
			$page->body .= $config->twig->render('items/itm/warehouse/form.twig', ['page' => $page, 'warehouse' => $item_warehouse, 'm_whse' => $itmw, 'recordlocker' => $recordlocker]);
			$page->body .= $config->twig->render('items/itm/warehouse/bins-modal.twig', ['page' => $page, 'itemID' => $itemID, 'm_whse' => $itmw]);
			$page->body .= $html->h3('class=mt-3', 'Notes');
			$page->body .= $config->twig->render('items/itm/warehouse/notes/notes.twig', ['page' => $page, 'item' => $item_warehouse, 'qnotes' => $qnotes]);
			$page->js   .= $config->twig->render('items/itm/warehouse/notes/order/js.twig', ['page' => $page, 'qnotes' => $qnotes]);

			// Warehouse Item JS
			$jsconfig = array('warehouses' => $itmw->get_itemwarehouses_js($itemID), 'in_defaultbin' => $itmw->configs->inventory->default_bin);
			$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => $jsconfig]);
			$page->js   .= $config->twig->render('items/itm/warehouse/js.twig', ['page' => $page, 'warehouse' => $item_warehouse, 'm_whse' => $itmw]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item $itemID does not exist in Warehouse $whseID"]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $html->a('class=btn btn-primary|href='.$page->itm_warehouseURL($itemID), $html->icon('fa fa-undo')." Back to $itemID Warehouses");
		}
	} else {
		$itmw->recordlocker->remove_lock($page->lockcode);
		$page->headline = "Warehouses for $itemID";
		$page->body .= $config->twig->render('items/itm/warehouse/description.twig', ['page' => $page, 'item' => $item]);
		$page->body .= $config->twig->render('items/itm/warehouse/list.twig', ['page' => $page, 'itmw' => $itmw, 'itemID' => $itemID, 'warehouses' => $itmw->get_itemwarehouses($itemID)]);
	}

	$session->remove('response_itm');

	include __DIR__ . "/basic-page.php";
