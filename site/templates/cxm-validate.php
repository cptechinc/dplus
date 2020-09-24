<?php
	/**
	 * CXM Validate
	 * This template is made for Validating Data Inputs for the CXM form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$rm = strtolower($input->requestMethod());
 	$values = $input->$rm;
	$cxm = $modules->get('XrefCxm');
	$validate = $modules->get('ValidateCxm');
	$response   = '';
	$returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-itemid':
				$itemID = $values->itemID ? $values->text('itemID') : $values->text('ouritemID');

				if ($cxm->validate_itemID($itemID)) {
					$response = true;
				} else {
					$response = "$itemID was not found in the Item Master";
				}
				break;
			case 'validate-cust-itemid':
				$custID     = $values->text('custID');
				$custitemID = $values->text('custitemID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($cxm->cxm_item_exists($custID, $custitemID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$custitemID from $custID was not found in the Customer X-ref";
				}
				break;
			case 'validate-cust-itemid-new':
				$custID     = $values->text('custID');
				$custitemID = $values->text('custitemID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($cxm->cxm_item_exists($custID, $custitemID)) {
					$response = ($returntype == 'bool') ? false : "$custitemID from $custID already exists in the Customer X-ref";
				} else {
					$response = true;
				}
				break;
			case 'validate-custid':
				$custID = $values->text('custID');

				if ($validate->custid($custID)) {
					$response = true;
				} else {
					$response = "$custID was not found in the Customer Master";
				}
				break;
		}
	}

	$page->body = json_encode($response);
