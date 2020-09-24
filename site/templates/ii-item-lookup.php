<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	
	switch ($values->text('entry')) {
		case 'po':
			$lookup = $modules->get('LookupItemEntryPo');
			break;
		default:
			$lookup = $modules->get('LookupItemEntry');
			break;
	}

	$response = $lookup->lookup_input($input);

	$page->body = json_encode($response);
