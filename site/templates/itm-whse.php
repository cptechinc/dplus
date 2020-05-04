<?php
	$itm = $modules->get('Itm');
	$itm_warehouse = $modules->get('ItmWarehouse');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			if ($input->requestMethod('POST') || $input->get->action) {
				$rm = strtolower($input->requestMethod());
				$itm_warehouse->process_input($input);
				$itemID = $input->$rm->text('itemID');
				//$session->redirect($page->itm_warehouseURL($itemID));
			}

			if ($session->response_itm) {
				$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
				$session->remove('response_itm');
			}

			$page->headline = "Warehouses for $itemID";
			$item = $itm->get_item($itemID);

			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/warehouse/description.twig', ['page' => $page, 'item' => $item]);
			$page->body .= $config->twig->render('items/itm/warehouse/list.twig', ['page' => $page, 'warehouses' => $itm_warehouse->get_warehouses($itemID)]);

		}
	} else {
		$session->redirect($page->itmURL());
	}

	include __DIR__ . "/basic-page.php";
