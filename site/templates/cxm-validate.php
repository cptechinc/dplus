<?php
	/**
	 * CXM Validate
	 * This template is made for Validating Data Inputs for the CXM form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$cxm = $modules->get('XrefCxm');
	$response   = '';
	$returntype = $input->get->return ? $input->get->text('return') : 'jqueryvalidate';

	if ($input->get->action) {
		switch ($input->get->text('action')) {
			case 'validate-itemid':
				$itemID = $input->get->itemID ? $input->get->text('itemID') : $input->get->text('ouritemID');

				if ($cxm->validate_itemID($itemID)) {
					$response = true;
				} else {
					$response = "$itemID was not found in the Item Master";
				}
				break;
			case 'validate-cust-itemid':
				$custID     = $input->get->text('custID');
				$custitemID = $input->get->text('custitemID');
				$returntype   = $input->get->return ? $input->get->text('return') : 'jqueryvalidate';

				if ($cxm->cxm_item_exists($custID, $custitemID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$itemID from $custID was not found in the Customer X-ref";
				}
				break;
		}
	}

	$page->body = json_encode($response);
