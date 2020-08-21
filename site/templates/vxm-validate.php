<?php
	/**
	 * VXM Validate
	 * This template is made for Validating Data Inputs for the VXM form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$vxm = $modules->get('XrefVxm');
	$response = '';

	if ($input->get->action) {
		switch ($input->get->text('action')) {
			case 'validate-itemid':
				$itemID = $input->get->itemID ? $input->get->text('itemID') : $input->get->text('ouritemID');

				if ($vxm->validate_itemID($itemID)) {
					$response = true;
				} else {
					$response = "$itemID was not found in the Item Master";
				}
				break;
			case 'validate-vendor-itemid-new':
				$vendorID     = $input->get->text('vendorID');
				$vendoritemID = $input->get->text('vendoritemID');

				if (!$vxm->vxm_item_exists($vendorID, $vendoritemID)) {
					$response = true;
				} else {
					$response = "$vendoritemID from $vendorID already exists";
				}
				break;
			case 'validate-vendor-itemid':
				$vendorID     = $input->get->text('vendorID');
				$vendoritemID = $input->get->text('vendoritemID');
				$returntype   = $input->get->return ? $input->get->text('return') : 'jqueryvalidate';

				if ($vxm->vxm_item_exists($vendorID, $vendoritemID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$itemID from $vendorID was not found in the Vendor X-ref";
				}
				break;
			case 'validate-primary-poordercode': // Returns if VXM Item can be used as Primary
				$ouritemID = $input->get->text('ouritemID');
				$vendoritemID = $input->get->text('vendoritemID');
				$returntype = $input->get->return ? 'jqueryvalidate' : 'bool';

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
