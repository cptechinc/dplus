<?php
	$lookup_item = $modules->get('LookupItem');
	$itemID = strtoupper($input->get->text('itemID'));

	$response = array('error' => false);

	if ($input->get->itemID) {
		if ($lookup_item->lookup_itm($itemID)) {
			$response['exists'] = true;

			if ($input->get->json) {
				$fields = $input->get->array('fields', null, ['delimiter' => ","]);
				$loader = $modules->get('LoadItem');
				$response['item'] = $loader->get_item_array($itemID, $fields);
			}
		} else {
			$response['exists'] = false;
			$response['message'] = "$itemID was not found in the Item Master";
		}
	} else {
		$response['error'] = true;
		$response['message'] = "No ItemID provided";
	}

	$page->body = json_encode($response);
