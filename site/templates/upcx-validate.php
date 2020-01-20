<?php
	/**
	 * UPCX Validate
	 * This template is made for Validating Data Inputs for the UPC form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$upcx = $modules->get('XrefUpc');
	$response = '';

	if ($input->get->action) {
		switch ($input->get->text('action')) {
			case 'validate-itemid':
				$itemID = $input->get->text('itemID');

				$q = ItemMasterItemQuery::create();
				$q->filterByItemid($itemID);

				if ($q->count()) {
					$response = true;
				} else {
					$response = "$itemID was not found in the Item Master";
				}
				break;
			case 'validate-upc':
				$code = $input->get->text('upc');
				$itemID = $input->get->text('itemID');

				if ($upcx->upc_exists($code)) {
					$upc = $upcx->get_upc($code);

					if ($upc->itemid == $itemID) {
						$response = true;
					} else {
						$response = "UPC $code already exists";
					}
				} else {
					$response = true;
				}
				break;
			case 'validate-primary-upc':
				$code = $input->get->text('upc');
				$itemID = $input->get->text('itemID');

				// Validate that there is a primary UPC
				if ($upcx->upc_primary_exists($itemID)) {
					$upc = $upcx->get_primary_upc_itemid($itemID);

					if ($upc->upc == $code) {
						$response = true;
					} else {
						$response = "$upc->upc";
					}
				} else {
					$response = true;
				}
				break;
		}
	}

	$page->body = json_encode($response);
