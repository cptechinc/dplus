<?php
	/**
	 * Purchase Order Validate
	 * This template is made for Validating Data Inputs for a Purchase Order
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$validate = $modules->get('ValidatePo');
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$response   = '';
	$returntype = $values->return ? $values->text('return') : 'jqueryvalidate';


	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-vendorid':
				$vendorID     = $values->text('vendorID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($validate->vendorid($vendorID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "'$vendorID' not found in the Vendor Master";
				}
				break;
			case 'validate-shipfromid':
				$vendorID     = $values->text('vendorID');
				$shipfromID     = $values->text('shipfromID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($validate->vendor_shipfromid($vendorID, $shipfromID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "Ship-From '$shipfromID' at $vendorID not found";
				}
				break;
		}
	}

	$page->body = json_encode($response);
