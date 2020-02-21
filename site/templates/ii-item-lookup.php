<?php
	$lookup = $modules->get('ItemEntryLookup');
	$response = $lookup->lookup_input($input);

	$response['sql'] = $con->getLastExecutedQuery();

	$page->body = json_encode($response);
