<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$kim = $modules->get('Kim');
	$kim->init_configs();
	$html = $modules->get('HtmlWriter');
	$page->show_breadcrumbs = false;

	if ($values->action) {
		$kim->process_input($input);
		$kitID = $values->text('kitID');
		$session->redirect($page->kitURL($kitID));
	}

	$page->body .= $config->twig->render('mki/kim/bread-crumbs.twig', ['page' => $page, 'input' => $input]);

	if ($session->response_kim) {
		$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_kim]);
	}

	if ($values->kitID) {
		$kitID = $values->text('kitID');
		$kit = $kim->new_get_kit($kitID);

		if (!$kit->isNew()) {
			$page->headline = "Kit Master: $kitID";
			/**
			 * Show alert that Kit is locked if
			 *  1. Kit isn't new
			 *  2. The Kit has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if (!$kim->lockrecord($kitID)) {
				$msg = "Kit $kitID is being locked by " . $kim->recordlocker->get_locked_user($kitID);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kitID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if ($kit->isNew()) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Kit $kitID does not exist", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You will be able to create this kit"]);
			$page->body .= $html->div('class=mb-3');
		}

		if ($values->component) {
			$itemID = $values->text('component');
			$component = $kim->component->new_get_component($kitID, $itemID);
			$page->headline = $itemID == 'new' ? "Kit Master: $kitID" : "Kit Master: $kitID - $itemID";
			$page->body .= $config->twig->render('mki/kim/kit/component/page.twig', ['page' => $page, 'kim' => $kim, 'kit' => $kit, 'component' => $component]);
			$page->js   .= $config->twig->render('mki/kim/kit/component/js.twig', ['page' => $page, 'kim' => $kim,]);
		} else {
			$page->body .= $config->twig->render('mki/kim/kit/page.twig', ['page' => $page, 'kim' => $kim, 'kit' => $kit]);
			$page->js   .= $config->twig->render('mki/kim/kit/js.twig', ['page' => $page, 'kim' => $kim]);
		}
	} else {
		$q = $input->get->text('q');
		$filter = $modules->get('FilterKim');
		$filter->init_query();
		if ($q) {
			$page->headline = "KIM: Searching for '$q'";
			$filter->search($q);
		}
		$kits = $filter->query->paginate($input->pageNum, $session->display);
		$page->body .= $config->twig->render('mki/kim/search-form.twig', ['page' => $page, 'q' => $q]);
		$page->body .= $config->twig->render('mki/kim/page.twig', ['page' => $page, 'kim' => $kim, 'kits' => $kits]);
		$page->js   .= $config->twig->render('mki/kim/list.js.twig', ['page' => $page, 'kim' => $kim]);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	include __DIR__ . "/basic-page.php";
