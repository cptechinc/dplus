<?php
	/**
	 * MAP JSON
	 * This template is made for validating, getting values for MAP Tables
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validate = $modules->get('ValidateMap');

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-vendorid':
				$vendorID = $values->text('vendorID');
				if ($validate->vendorid($vendorID)) {
					$response = false;
				} else {
					$response = "Vendor $vendorID not found";
				}
				break;
			case 'validate-vxm-item-itemid':
				$vendorID = $values->text('vendorID');
				$itemID   = $values->text('itemID');
				$vendoritemID   = $values->text('vendoritemID');
				
				if ($validate->vxm->exists($vendorID, $vendoritemID, $itemID)) {
					$q = ItemXrefVendorQuery::create();
					$q->filterByItemid($itemID)->filterByVendorid($vendorID);
					$q->filterByVendoritemid($vendoritemID);
					$response = boolval($q->count()) ? true : "Vendor $vendorID Item $vendortemID is not for $itemID";
				}  else {
					$response = "Vendor $vendorID Item $vendortemID does not exist in the X-ref";
				}
				break;
			case 'validate-vxm-itemid-exists':
				$vendorID = $values->text('vendorID');
				$itemID   = $values->text('itemID');
				$response = $validate->vxm->vendor_has_xref_itemid($itemID, $vendorID);
				break;
			case 'count-vxm-itemid':
				$vendorID = $values->text('vendorID');
				$itemID   = $values->text('itemID');

				if ($validate->vxm->vendor_has_xref_itemid($vendorID, $itemID)) {
					$q = ItemXrefVendorQuery::create()->filterByItemid($itemID)->filterByVendorid($vendorID);
					$response = $q->count();
				} else {
					$response = false;
				}
				break;
			case 'get-vxm-itemid': // NOT FOR JQUERYVALIDATE
				$vendorID = $values->text('vendorID');
				$itemID   = $values->text('itemID');

				if ($validate->vxm->vendor_has_xref_itemid($vendorID, $itemID)) {
					$q = ItemXrefVendorQuery::create()->filterByItemid($itemID)->filterByVendorid($vendorID);
					if ($validate->vxm->vendor_has_primary($vendorID, $itemID)) {
						$q->filterByPo_ordercode(ItemXrefVendor::POORDERCODE_PRIMARY);
					}
					$xref = $q->findOne();
					$response = array(
						'vendorid'     => $vendorID,
						'itemid'       => $itemID,
						'vendoritemid' => $xref->vendoritemid
					);
				} else {
					$response = false;
				}
				break;
			case 'get-vxm-xref': // NOT FOR JQUERYVALIDATE
				$vendorID = $values->text('vendorID');
				$vendoritemID   = $values->text('vendoritemID');
				$itemID   = $values->text('itemID');

				if ($validate->vxm->exists($vendorID, $vendoritemID, $itemID)) {
					$xref = $modules->get('XrefVxm')->xref($vendorID, $vendoritemID, $itemID);
					$response = array(
						'vendorid'     => $vendorID,
						'itemid'       => $itemID,
						'vendoritemid' => $xref->vendoritemid
					);
				} else {
					$response = false;
				}
				break;
			case 'validate-mxrfe-xref':
				$mnfrID = $values->text('mnfrID');
				$mnfritemID = $values->text('mnfritemID');
				$itemID = $values->text('itemID');

				if ($validate->mxrfe->exists($mnfrID, $mnfritemID, $itemID)) {
					$response = true;
				} else {
					$response = "MXRFE X-ref not found";
				}
				break;
			case 'validate-mxrfe-xref-new':
				$mnfrID = $values->text('mnfrID');
				$mnfritemID = $values->text('mnfritemID');
				$itemID = $values->text('itemID');

				if ($validate->mxrfe->exists($mnfrID, $mnfritemID, $itemID) == false) {
					$response = true;
				} else {
					$response = "MXRFE X-ref exists";
				}
				break;
		}
	}

	$page->body = json_encode($response);
