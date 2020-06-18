<?php
	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;
	$qnotes = $modules->get('QnotesPredefined');

	if ($values->action) {
		$qnotes->process_input($input);
		$session->redirect($page->url, $http301 = false);
	}

	if ($session->response_qnote) {
		$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
		$session->remove('response_qnote');
	}

	$page->body .= $config->twig->render('msa/noce/list.twig', ['page' => $page, 'qnotes' => $qnotes]);
	$page->body .= $config->twig->render('msa/noce/notes-modal.twig', ['page' => $page]);
	$page->js   .= $config->twig->render("msa/noce/js.twig", ['page' => $page, 'qnotes' => $qnotes]);

	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
