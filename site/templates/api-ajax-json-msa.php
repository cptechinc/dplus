<?php
	/**
	 * MSA JSON
	 * This template is made for validating, getting values for MSA Codes
	 * NOTE: the response values can be formatted to be used by Jquery Validate's remote validation method
	 */
	$response   = '';
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$validate = $modules->get('ValidateMsa');

	// NOTE USE WHEN NEEDED FOR JQUERYVALIDATE
	// $returntype = $values->return ? $values->text('return') : 'jqueryvalidate';

	if ($values->action) {
		switch ($values->text('action')) {
			case 'validate-loginid':
				$loginID = $values->text('loginID') ? $values->text('loginID') : $values->text('userID');

				if ($validate->loginid($loginID)) {
					$response = true;
				} else {
					$response = "User $loginID not found";
				}
				break;
			case 'get-user': // NOT FOR JQUERYVALIDATE
				$loginID = $values->text('loginID');

				if ($validate->loginid($loginID)) {
					$q = DplusUserQuery::create();
					$q->filterByLoginid($loginID);
					$login = $q->findOne();
					$response = array(
						'loginid' => $loginID,
						'name'    => $login->name,
						'whseid'  => $login->whseid,
					);
				} else {
					$response = false;
				}
				break;
		}
	}

	$page->body = json_encode($response);
