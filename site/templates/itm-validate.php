<?php
	/**
	 * ITM Validate
	 * This template is made for Validating Data Inputs for the CRCD form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validator = $modules->get('ValidateItm');

	$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-custid':
				$custID = $values->text('custID');

				if ($validator->custid($custID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$custID was not found in the Customer Master";
				}
				break;
			case 'validate-itemid-exists':
				$itemID = $values->text('itemID');

				if ($validator->itemid_exists($itemID)) {
					if ($returntype == 'obj') {
						$loader = $modules->get('LoadItem');
						$response = array(
							'exists' => true,
							'item'   => $loader->get_item_array($itemID, [])
						);
					} else {
						$response = true;
					}
				} else {
					$response = ($returntype == 'bool') ? false : "$itemID was not found in the Item Master";
				}
				break;
		}
	}

	$page->body = json_encode($response);
