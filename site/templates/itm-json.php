<?php
	use Dplus\CodeValidators\Min as MinValidator;
	$validate = new MinValidator();
	$itemID = strtoupper($input->get->text('itemID'));

	$response = array('error' => false);

	if ($input->get->itemID) {
		if ($validate->itemid($itemID)) {
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
