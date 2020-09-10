<?php
	/**
	 * CI JSON
	 * This template is made for validating, getting values for Customer
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validate_vendorid = $modules->get('LookupVendor');

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-vendorid':
				$vendorID = $values->text('vendorID');

				if ($validate_vendorid->lookup_vendor($vendorID)) {
					$response = true;
				} else {
					$response = "$vendorID  was not found in the Vendor Master";
				}
				break;
			case 'get-vendor-name': // NOT FOR JQUERYVALIDATE
				$vendorID = $values->text('vendorID');

				if ($validate_vendorid->lookup_vendor($vendorID)) {
					$vendor = VendorQuery::create()->findOneById($vendorID);
					$response = $vendor->name;
				} else {
					$response = false;
				}
				break;
			case 'get-vendor-address': // NOT FOR JQUERYVALIDATE
				$vendorID = $values->text('vendorID');

				if ($validate_vendorid->lookup_vendor($vendorID)) {
					$vendor = VendorQuery::create()->findOneById($vendorID);
					$response = array(
						'name'     => $vendor->name,
						'address'  => $vendor->address,
						'address2' => $vendor->address2,
						'city'     => $vendor->city,
						'state'    => $vendor->state,
						'zip'      => $vendor->zip
					);
				} else {
					$response = false;
				}
				break;
		}
	}

	$page->body = json_encode($response);
