<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->code) {
		$code = $input->get->text('code');
		$configSO = ConfigSalesOrderQuery::create()->findOne();
		$countries = CountryQuery::create()->find();
	 	$creditcards = CreditCardDigitGetQuery::create()->find();

		if ($module_codetable->code_exists($code)) {
			$page->title = $page->headline = "Customer Terms Code: $code";
			$termscode = $module_codetable->get_code($code);
		} else {
			$page->title = $page->headline = "Create $page->title";
			$termscode = new CustomerTermsCode;

			if ($code != 'new') {
				$termscode->setCode($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Type Code '$code' could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if (!$termscode->isNew()) {
			/**
			 * Show alert that termscode is locked if
			 *  1. Termscode Isn't new
			 *  2. The termscode has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $termscode->code) && !$recordlocker->function_locked_by_user($page->codetable, $termscode->code)) {
				$msg = "$termscode->code is being locked by " . $recordlocker->get_locked_user($page->codetable, $termscode->code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Terms Code $termscode->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $termscode->code)) {
				$recordlocker->create_lock($page->codetable, $termscode->code);
			}
		}

		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $termscode, 'countries' => $countries, 'creditcards' => $creditcards, 'configso' => $configSO, 'recordlocker' => $recordlocker, 'm_trm' => $module_codetable]);
		$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js/js.twig", ['page' => $page, 'configso' => $configSO, 'termscode' => $termscode, 'm_trm' => $module_codetable]);
		$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js/splits.js.twig", ['page' => $page, 'configso' => $configSO, 'termscode' => $termscode, 'm_trm' => $module_codetable]);
	} else {
		$page->title = $page->headline = "Customer Terms Code";
		$recordlocker->remove_lock($page->codetable);
		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$page->codetable-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
