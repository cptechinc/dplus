<?php
	/**
	 * CRCD Validate
	 * This template is made for Validating Data Inputs for the CRCD form
	 * NOTE: the response values are formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-custid':
				$validate = new Dplus\CodeValidators\Mar();
				$custID = $values->text('custID');
				$returntype   = $values->return ? $values->text('return') : 'jqueryvalidate';

				if ($validate->custid($custID)) {
					$response = true;
				} else {
					$response = ($returntype == 'bool') ? false : "$custID was not found in the Customer Master";
				}
				break;
		}
	}
	$page->body = json_encode($response);
