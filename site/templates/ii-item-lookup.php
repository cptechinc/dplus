<?php
	$lookup = $modules->get('LookupItemEntry');
	$response = $lookup->lookup_input($input);

	$page->body = json_encode($response);
