<?php
	$q = $input->get->text('q');
	$page->searchURL = $page->url;
	$count = 0

	switch ($page->ajaxcode) {
		case 'tariff-codes':
			$filter = $modules->get('FilterCountryCodes');
			break;
	}

	$filter->init_query($user);
	if ($input->get->q) {
		$filter->filter_search($q);
		$page->headline = "Searching for '$q'";
	}
	$filter->apply_sortby($page);
	$query = $filter->get_query();

	switch ($page->ajaxcode) {
		case 'tariff-codes':
			$countries = $query->paginate($input->pageNum, 10);
			$count = $countries->getNbResults();
			$page->body = $config->twig->render('api/lookup/country-codes/search.twig', ['page' => $page, 'countries' => $countries, 'datamatcher' => $modules->get('RegexData'), 'q' => $q]);
			break;
	}

	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $count]);

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
