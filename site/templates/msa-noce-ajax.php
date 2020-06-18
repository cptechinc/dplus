<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$q = $values->text('q');
	$page->title = $q ? "Note results for '$q'" : "Predefined Notes";
	$qnotes = $modules->get('QnotesPredefined');
	$filter_notes = $modules->get('FilterQnotesPredefined');
	$filter_notes->init_query($user);
	$filter_notes->filter_sequence();
	$filter_notes->filter_search($q);
	$filter_notes->apply_sortby($page);
	$query = $filter_notes->get_query();
	$notes = $query->paginate($input->pageNum, 10);

	$page->searchURL = $page->url;
	$page->body .= $config->twig->render('msa/noce/ajax/form.twig', ['page' => $page]);
	$page->body .= $config->twig->render('msa/noce/ajax/list.twig', ['page' => $page, 'qnotes' => $qnotes, 'notes' => $notes]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $notes->getNbResults()]);

	if ($config->ajax) {
		echo $page->body;
	} else {
		$page->js .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page, 'qnotes' => $qnotes, 'notes' => $notes]);
		include __DIR__ . "/basic-page.php";
	}
