<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->code) {
		$code = $whseID = $input->get->text('code');
		$states = StatesQuery::create()->find();
		$countries = CountryQuery::create()->find();
		$warehouses = WarehouseQuery::create()->find();
		$config_in = ConfigInQuery::create()->findOne();

		if ($module_codetable->code_exists($code)) {
			$page->title = $page->headline = "IWHM: $code";
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


		if (!$warehouse->isNew()) {
			/**
			 * Show alert that warehouse is locked if
			 *  1. Warehouse Isn't new
			 *  2. The warehouse has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $warehouse->id) && !$recordlocker->function_locked_by_user($page->codetable, $warehouse->id)) {
				$msg = "$warehouse->id is being locked by " . $recordlocker->get_locked_user($page->codetable, $warehouse->id);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Warehouse $warehouse->id is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $warehouse->id)) {
				$recordlocker->create_lock($page->codetable, $warehouse->id);
			}
		}

		$page->customerlookupURL = $pages->get('pw_template=mci-lookup')->url;
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'warehouse' => $warehouse, 'config_in' => $config_in, 'states' => $states, 'countries' => $countries, 'warehouses' => $warehouses, 'recordlocker' => $recordlocker]);
		$page->body .= $config->twig->render("util/ajax-modal.twig", []);
		$page->js   .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'warehouse' => $warehouse]);

		// SHOW NOTES IF TABLE ALREADY EXISTS
		if ($module_codetable->code_exists($code)) {
			$module_notes = $modules->get('CodeTablesIwhmNotes');

			// TODO:: Notes Editing
			$query_notes = WarehouseNoteQuery::create();
			$query_notes->filterByWhse($whseID);
			$query_notes->filterBySequence(1);
			$notes = $query_notes->find();
			$page->body .= $config->twig->render("code-tables/min/$page->codetable/notes.twig", ['page' => $page, 'warehouse' => $warehouse, 'notes' => $notes, 'module_notes' => $module_notes]);
			$page->body .= $config->twig->render("code-tables/min/$page->codetable/notes-modal.twig", ['page' => $page, 'warehouse' => $warehouse]);
		}
	} else {
		$recordlocker->remove_lock($page->codetable);
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'warehouses' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}

//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
