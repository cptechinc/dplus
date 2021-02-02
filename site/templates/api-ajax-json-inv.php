<?php
	/**
	 * Inventory JSON
	 * This template is made for validating, getting values for Inventory
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validate = new Dplus\CodeValidators\Min();

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-tariffcode':
				$code = $values->text('code') ? $values->text('code') : $values->text('tariffcode');

				if ($validate->tariffcode($code)) {
					$response = true;
				} else {
					$response = "Tariff Code $code not found";
				}
				break;
			case 'get-tariffcode': // NOT FOR JQUERYVALIDATE
				$code = $values->text('code');

				if ($validate->tariffcode($code)) {
					$tariff = TariffCodeQuery::create()->findOneByCode($code);
					$response = array(
						'code'        => $code,
						'number'      => $tariff->number,
						'rate'        => $tariff->duty_rate,
						'description' => $tariff->description
					);
				} else {
					$response = false;
				}
				break;
			case 'validate-countrycode':
				$code = $values->text('code') ? $values->text('code') : $values->text('countrycode');

				if ($validate->countrycode($code)) {
					$response = true;
				} else {
					$response = "Country Code $code not found";
				}
				break;
			case 'get-countrycode':
				$code = $values->text('code');

				if ($validate->countrycode($code)) {
					$tariff = CountryCodeQuery::create()->findOneByCode($code);
					$response = array(
						'code'        => $code,
						'description' => $tariff->description
					);
				} else {
					$response = false;
				}
				break;
			case 'validate-msdscode':
				$code = $values->text('code') ? $values->text('code') : $values->text('msdscode');

				if ($validate->msdscode($code)) {
					$response = true;
				} else {
					$response = "MSDS $code not found";
				}
				break;
			case 'get-msdscode':
				$code = $values->text('code');

				if ($validate->msdscode($code)) {
					$msds = $modules->get('CodeTablesMsdsm')->get_code($code);
					$response = array(
						'code'        => $code,
						'description' => $msds->description
					);
				} else {
					$response = false;
				}
				break;
			case 'validate-itemid':
				$itemID = $values->text('itemID');

				if ($validate->itemid($itemID)) {
					$response = true;
				} else {
					$response = "$itemID not found";
				}
				break;
			case 'get-item':
				$itemID = $values->text('itemID');

				if ($validate->itemid_exists($itemID)) {
					$fields = $input->get->array('fields', null, ['delimiter' => ","]);
					$loader = $modules->get('LoadItem');
					$response = $loader->get_item_array($itemID, $fields);
				} else {
					$response = false;
				}
				break;
			case 'validate-itmp-exists':
				$loginID = $values->text('loginID') ? $values->text('loginID') : $values->text('userID');
				$itmp = $modules->get('Itmp');

				if ($itmp->exists($loginID)) {
					$response = true;
				} else {
					$response = "ITMP for $loginID not found";
				}
				break;
			case 'validate-warehouseid':
				$whseID = $values->text('whseID');
				if ($validate->whseid($whseID) || $whseID == '**') {
					$response = true;
				} else {
					$response = "Warehouse ID $whseID not found";
				}
				break;
		}
	}

	$page->body = json_encode($response);
