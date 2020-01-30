<?php
	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemID);

		if ($q->count()) {
			$response = true;
		} else {
			$response = "$itemID was not found in the Item Master";
		}

		$page->body = json_encode($response);
	}
