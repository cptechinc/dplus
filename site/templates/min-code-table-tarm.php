<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";
		$countries = CountryCodeQuery::create()->find();

		if ($module_codetable->code_exists($code)) {
			$tariff = $module_codetable->get_code($code);
		} else {
			$tariff = new TariffCode();
			$page->headline = "Create New Tariff Class";

			if ($code != 'new') {
				$tariff->setCode($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Type Code '$code' could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if (!$tariff->isNew()) {
			/**
			 * Show alert that tariff is locked if
			 *  1. tariff Isn't new
			 *  2. The tariff has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $tariff->code) && !$recordlocker->function_locked_by_user($page->codetable, $tariff->code)) {
				$msg = "$tariff->code is being locked by " . $recordlocker->get_locked_user($page->codetable, $tariff->code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Tariff $tariff->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $tariff->code)) {
				$recordlocker->create_lock($page->codetable, $tariff->code);
			}
		}

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'countries' => $countries, 'tariff' => $tariff, 'recordlocker' => $recordlocker]);
		$page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'tariffcode' => $tariff, 'm_tarm' => $module_codetable]);
	} else {
		$recordlocker->remove_lock($page->codetable);
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}
