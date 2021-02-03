<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	/**
	 * VXM Validate
	 * This template is made for Validating Data Inputs for the VXM form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$vxm = $modules->get('XrefVxm');
	$validate = new Dplus\CodeValidators\Map\Vxm();
	$vxm->init_configs();
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
			case 'validate-xref':
				$vendorID     = $values->text('vendorID');
				$vendoritemID = $values->text('vendoritemID');
				$itemID = $values->text('itemID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($vxm->xref_exists($vendorID, $vendoritemID, $itemID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$vendoritemID from $vendorID for $itemID was not found in the Vendor X-ref";
				}
				break;
			case 'validate-xref-new':
				$vendorID     = $values->text('vendorID');
				$vendoritemID = $values->text('vendoritemID');
				$itemID       = $values->text('itemID');

				if (!$vxm->xref_exists($vendorID, $vendoritemID, $itemID)) {
					$response = true;
				} else {
					$response = "$vendoritemID from $vendorID for $itemID already exists";
				}
				break;
			case 'validate-primary-poordercode': // Returns if VXM Item can be used as Primary
				$vendorID     = $values->text('vendorID');
				$vendoritemID = $values->text('vendoritemID');
				$itemID       = $values->text('ouritemID');

				$returntype  = $values->return ? 'bool' : 'jqueryvalidate';

				if ($vxm->poordercode_primary_exists($itemID)) {
					$primary = $vxm->get_primary_poordercode_itemid($itemID);

					if ($primary->vendorid == $vendorID && $primary->vendoritemid == $vendoritemID) {
						$response = true;
					} else {
						$response = ($returntype == 'bool') ? false : "Item $primary->ouritemID has another Primary Vendor Item";
					}
				} else {
					$response = true;
				}
				break;
			case 'validate-update-itm-cost':
				$response = ['allow' => false, 'confirm' => false];
				$vendorID     = $values->text('vendorID');
				$vendoritemID = $values->text('vendoritemID');
				$itemID       = $values->text('itemID');
				$ordercode    = $values->text('ordercode');

				if ($vxm->xref_exists($vendorID, $vendoritemID, $itemID)) {
					$xref = $vxm->xref($vendorID, $vendoritemID, $itemID);

					if (array_key_exists($ordercode, ItemXrefVendor::OPTIONS_POORDERCODE)) {
						$xref->setPo_ordercode($ordercode);
					}

					$response['allow'] = $vxm->allow_itm_cost_update_xref($xref);

					if ($response['allow']) {
						$response['confirm'] = $vxm->configs->ap->confirm_update_itm_cost();
					}
				}
				break;
			case 'get-item':
				$itemID = $values->itemID ? $values->text('itemID') : $values->text('ouritemID');

				if ($validate->itemid($itemID)) {
					$item = ItemMasterItemQuery::create()->findOneByItemid($itemID);
					$response = array(
						'itemid' => $itemID,
						'description' => $item->description,
						'description2' => $item->description2,
						'uom' => array(
							'sale' => array(
								'code'        => $item->UnitofMeasureSale ? $item->UnitofMeasureSale->code : '',
								'description' => $item->UnitofMeasureSale ? $item->UnitofMeasureSale->description : '',
								'conversion'  => $item->UnitofMeasureSale ? $item->UnitofMeasureSale->conversion : '',
							)
						),
						'iskit' => $item->is_kit(),
						'pricing' => [
							'base' => $item->pricing->baseprice
						],
						'standardcost' => [
							'base' => $item->standardcostbasedon
						]
					);
				} else {
					$response = false;
				}
				break;
		}
	}

	$page->body = json_encode($response);
