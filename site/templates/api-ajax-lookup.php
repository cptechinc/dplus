<?php
	$q = $input->get->text('q');
	$page->searchURL = $page->url;
	$count = 0;

	switch ($page->ajaxcode) {
		case 'tariff-codes':
			$filter = $modules->get('FilterInvTariffCodes');
			break;
		case 'msds-codes':
			$filter = $modules->get('FilterInvMsdsCodes');
			break;
		case 'freight-codes':
			$filter = $modules->get('FilterMsoFreightCodes');
			break;
		case 'users':
			$filter = $modules->get('FilterDplusUsers');
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
		default:
			$twigloader = $config->twig->getLoader();
			if ($twigloader->exists("api/lookup/$page->ajaxcode/search.twig")) {
				$results = $query->paginate($input->pageNum, 10);
				$count   = $results->getNbResults();
				$page->body .= $config->twig->render("api/lookup/$page->ajaxcode/search.twig", ['page' => $page, 'results' => $results, 'datamatcher' => $modules->get('RegexData'), 'q' => $q]);
			} else {
				$page->body = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "$page->ajaxcode lookup does not exist"]);
			}
			break;
	}

	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $count]);

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
