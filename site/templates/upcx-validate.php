<?php
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
				$upc = $upcx->get_upc($code);

				if ($upc->is_primary()) {
					return true;
				} else {
					$primaryupc = $upc->get_primary_upc_code();

					if ($primaryupc) {
						$response = "$primaryupc";
					} else {
						$response = true;
					}
				}

				break;
		}
	}

	$page->body = json_encode($response);
