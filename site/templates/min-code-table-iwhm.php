<?php
	$html = $modules->get('HtmlWriter');

	if ($input->get->code) {
		$code = $whseID = $input->get->text('code');
		$states = StatesQuery::create()->find();
		$countries = CountryQuery::create()->find();
		$warehouses = WarehouseQuery::create()->find();
		$config_in = ConfigInQuery::create()->findOne();

		if ($module_codetable->code_exists($code)) {
			$page->title = $page->headline = "Editing $page->title $code";
			$warehouse = $module_codetable->get_code($code);
		} else {
			$page->title = $page->headline = "Create new Warehouse";
			$warehouse = new Warehouse();

			if ($code != 'new') {
				$warehouse->setId($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Warehouse $code could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'warehouse' => $warehouse, 'config_in' => $config_in, 'states' => $states, 'countries' => $countries, 'warehouses' => $warehouses]);

		// SHOW NOTES IF TABLE ALREADY EXISTS
		if ($module_codetable->code_exists($code)) {
			// TODO:: Notes Editing
			$query_notes = WarehouseNoteQuery::create();
			$query_notes->filterByWhse($whseID);
			$query_notes->filterBySequence(1);
			$notes = $query_notes->find();
			$page->body .= $config->twig->render("code-tables/min/$page->codetable/notes.twig", ['page' => $page, 'notes' => $notes]);
		}
	} else {
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'warehouses' => $module_codetable->get_codes(), 'response' => $session->response_codetable]);
	}

//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
