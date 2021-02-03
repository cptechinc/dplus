<?php
	/**
	 * UPCX Validate
	 * This template is made for Validating Data Inputs for the UPC form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$upcx = $modules->get('XrefUpc');
	$response = '';
	$validate = new Dplus\CodeValidators\Min\Upcx;

	if ($input->get->action) {
		switch ($input->get->text('action')) {
			case 'validate-itemid':
				$itemID = $input->get->text('itemID');

				if ($validate->itemid($itemID)) {
					$response = true;
				} else {
					$response = "$itemID was not found in the Item Master";
				}
				break;
			case 'validate-upc':
				$code = $input->get->text('upc');
				$itemID = $input->get->text('itemID');

				if ($validate->exists($code)) {
					$upc = $upcx->xref($code);

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
				if ($upcx->xref_primary_exists_for_itemid($itemID)) {
					$upc = $upcx->xref_primary_by_itemid($itemID);

					if ($upc->upc == $code) {
						$response = true;
					} else {
						$response = "$upc->upc";
					}
				} else {
					$response = true;
				}
				break;
			case 'get-item':
				$itemID = $input->get->text('itemID');

				if ($validate->itemid($itemID)) {
					$item = ItemMasterItemQuery::create()->findOneByItemid($itemID);
					$primaryupc = $upcx->upc_primary_exists($itemID) ? $upcx->xref_primary_by_itemid($itemID)->upc : false;
					$response = array(
						'itemid' => $itemID	,
						'description'  => $item->description,
						'description2' => $item->description2,
						'primaryupc'   => $primaryupc
					);
				} else {
					$response = false;
				}
				break;
		}
	}

	$page->body = json_encode($response);
