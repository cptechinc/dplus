<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	/**
	 * VXM Validate
	 * This template is made for Validating Data Inputs for the VXM form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$vxm = $modules->get('XrefVxm');
	$validate = $modules->get('ValidateVxm');
	$response = '';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-itemid':
				$itemID = $values->itemID ? $values->text('itemID') : $values->text('ouritemID');

				if ($validate->itemid($itemID)) {
					$response = true;
				} else {
					$response = "$itemID was not found in the Item Master";
				}
				break;
			case 'validate-vendorid':
				$vendorID = $values->text('vendorID');

				if ($validate->vendorid($vendorID )) {
					$response = true;
				} else {
					$response = "$vendorID was not found in the Vendor Master";
				}
				break;
			case 'validate-vendor-itemid':
				$vendorID     = $values->text('vendorID');
				$vendoritemID = $values->text('vendoritemID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($vxm->vxm_item_exists($vendorID, $vendoritemID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$itemID from $vendorID was not found in the Vendor X-ref";
				}
				break;
			case 'validate-vendor-itemid-new':
				$vendorID     = $values->text('vendorID');
				$vendoritemID = $values->text('vendoritemID');

				if (!$vxm->vxm_item_exists($vendorID, $vendoritemID)) {
					$response = true;
				} else {
					$response = "$vendoritemID from $vendorID already exists";
				}
				break;
			case 'validate-primary-poordercode': // Returns if VXM Item can be used as Primary
				$ouritemID = $values->text('ouritemID');
				$vendoritemID = $values->text('vendoritemID');
				$returntype  = $values->return ? 'bool' : 'jqueryvalidate';

				if ($vxm->poordercode_primary_exists($ouritemID)) {
					$primary = $vxm->get_primary_poordercode_itemid($ouritemID);

					if ($primary->vendoritemid == $vendoritemID) {
						$response = true;
					} else {
						$response = ($returntype == 'bool') ? false : "Item $primary->ouritemID has another Primary Vendor Item";
					}
				} else {
					$response = true;
				}
				break;
		}
	}

	$page->body = json_encode($response);
