<?php
	/**
	 * MCI JSON
	 * This template is made for validating, getting values for MCI Tables
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-custid':
				$custID = $values->text('custID');
				$q = CustomerQuery::create()->filterByCustid($custID);
				
				if ($q->count()) {
					$response = true;
				} else {
					$response = "Customer $custID not found in the Customer Master";
				}
				break;
		}
	}

	$page->body = json_encode($response);
