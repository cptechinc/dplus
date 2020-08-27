<?php
	$filter = $modules->get('FilterCountryCodes');
	$filter->init_query($user);

	$q = $input->get->text('q');

	if ($input->get->q) {
		$filter->filter_search($q);
		$page->headline = "Searching for '$q'";
	}

	$filter->apply_sortby($page);
	$query = $filter->get_query();
	$countries = $query->paginate($input->pageNum, 10);

	$page->searchURL = $page->url;
	$page->body = $config->twig->render('api/lookup/country-codes/search.twig', ['page' => $page, 'countries' => $countries, 'datamatcher' => $modules->get('RegexData'), 'q' => $q]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $countries->getNbResults()]);

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
