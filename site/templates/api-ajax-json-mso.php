<?php
	/**
	 * CI JSON
	 * This template is made for validating, getting values for Customer
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validate = $modules->get('ValidateSo');

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-freightcode':
				$code = $values->text('code') ? $values->text('code') : $values->text('freightcode');

				if ($validate->freightcode($code)) {
					$response = true;
				} else {
					$response = "Freight Code $code not found";
				}
				break;
			case 'get-freightcode': // NOT FOR JQUERYVALIDATE
				$code = $values->text('code');

				if ($validate->freightcode($code)) {
					$freight = $modules->get('CodeTablesMfcm')->get_code($code);
					$response = array(
						'code'        => $code,
						'description' => $freight->description
					);
				} else {
					$response = false;
				}
				break;
			case 'validate-price-margin':
				$discounter = $modules->get('PriceDiscounter');
				$discounter->setItemid($values->text('itemID'));
				$discounter->setPrice($values->float('price'));

				if ($discounter->allowPrice()) {
					$response = true;
				} else {
					$response = false;
				}
				break;
			case 'get-lowest-price':
				$discounter = $modules->get('PriceDiscounter');
				$discounter->setItemid($values->text('itemID'));
				$discounter->setPrice($values->float('price'));
				$response = $discounter->minprice();
				break;
		}
	}

	$page->body = json_encode($response);
