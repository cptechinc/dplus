<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->code) {
		$code = $input->get->text('code');
		$page->headline = "Editing $page->title $code";
		$itemgroup = $module_codetable->get_code($code);

		if ($module_codetable->code_exists($code)) {
			$itemgroup = $module_codetable->get_code($code);
		} else {
			$itemgroup = new InvGroupCode();

			if ($code != 'new') {
				$itemgroup->setCode($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Type Code '$code' could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if (!$itemgroup->isNew()) {
			/**
			 * Show alert that itemgroup is locked if
			 *  1. itemgroup Isn't new
			 *  2. The itemgroup has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $itemgroup->code) && !$recordlocker->function_locked_by_user($page->codetable, $itemgroup->code)) {
				$msg = "$itemgroup->code is being locked by " . $recordlocker->get_locked_user($page->codetable, $itemgroup->code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "itemgroup $itemgroup->code is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $itemgroup->code)) {
				$recordlocker->create_lock($page->codetable, $itemgroup->code);
			}
		}

		$product_line_codes = InvProductLineCodeQuery::create()->find();
		$gl_accounts = GlCodeQuery::create()->find();

		$page->body .= $config->twig->render("code-tables/min/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'itemgroup' => $itemgroup, 'product_line_codes' => $product_line_codes, 'gl_accounts' => $gl_accounts, 'recordlocker' => $recordlocker]);
        $page->js .= $config->twig->render("code-tables/min/$page->codetable/js.twig", ['page' => $page, 'itemgroup' => $itemgroup]);
	} else {
		$recordlocker->remove_lock($page->codetable);
		$page->body .= $config->twig->render("code-tables/min/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}
