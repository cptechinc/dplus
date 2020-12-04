<?php
	/**
	 * Kits JSON
	 * This template is made for validating, getting values for MKI
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validate = $modules->get('ValidateKim');

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-kitid':
				$kitID = $values->text('kitID');

				if ($validate->kit_exists($kitID)) {
					$response = true;
				} else {
					$response = "Kit ID $kitID not found";
				}
				break;
			case 'validate-kitid-new': // Validate that Kit does not exist.
				$kitID = $values->text('kitID');

				if ($validate->kit_exists($kitID)) {
					$response = "Kit ID $kitID already exists";
				} else {
					$response = true;
				}
				break;
			case 'validate-can-delete-kit': // Validate that Kit can be deleted
				$kitID = $values->text('kitID');

				if ($validate->can_delete($kitID)) {
					$response = true;
				} else {
					$response = "Cannot delete Kit ID $kitID. It has committed orders.";
				}
				break;
			case 'get-kit': // NOT FOR JQUERYVALIDATE
				$kitID = $values->text('kitID');

				if ($validate->kit_exists($kitID)) {
					$kit = InvKitQuery::create()->findOneByItemid($kitID);
					$response = array(
						'kitid'       => $kitID,
						'description' => $kit->item->description
					);
				} else {
					$response = false;
				}
				break;
			case 'validate-kit-comoponent':
				$kitID = $values->text('kitID');
				$component = $values->component ? $values->text('component') : $values->text('itemID');

				if ($validate->kit_component_exists($kitID, $component)) {
					$response = true;
				} else {
					$response = "Kit ID $kitID Component $component not found";
				}
				break;
		}
	}

	$page->body = json_encode($response);
