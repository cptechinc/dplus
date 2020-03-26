<?php
	$page->formurl = $page->parent->child('template=redir')->url;

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Print Item Label: Results for '$scan'";
		$query_phys = WhseitemphysicalcountQuery::create();
		$query_phys->filterBySessionid(session_id());
		$query_phys->filterByScan($scan);

		if ($query_phys->count()) {
			$item = $query_phys->findOne();
			$page->body .= $config->twig->render('warehouse/inventory/print-item-label/provalley/label-form.twig', ['page' => $page, 'item' => $item]);
			$page->js   .= $config->twig->render('warehouse/inventory/print-item-label/provalley/js.twig');

		} else {
			$session->redirect($page->url);
		}
	} else {
		$page->body .= $config->twig->render('warehouse/inventory/print-item-label/provalley/item-form.twig', ['page' => $page]);
	}

	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	include __DIR__ . "/basic-page.php";
