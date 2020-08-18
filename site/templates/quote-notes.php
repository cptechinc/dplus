<?php
	$modules->get('DpagesMqo')->init_mqopage();
	$qnotes = $modules->get('QnotesQuote');
	$lookup_quotes = $modules->get('LookupQuote');

	if ($input->requestMethod('POST')) {
		$qnotes->process_input($input);
		$page->fullURL->query->remove('linenbr');
		$session->redirect($page->fullURL->getURL());
	} else {
		if ($input->get->qnbr) {
			$qnbr = $input->get->text('qnbr');

			if ($lookup_quotes->lookup($qnbr)) {
				$quote = QuoteQuery::create()->filterByQuoteid($qnbr)->findOne();
				$page->title = "Quote #$qnbr Notes";

				if ($session->response_qnote) {
					$page->body .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
				}
				$page->body .= $config->twig->render('quotes/quote/notes/qnotes-page.twig', ['page' => $page, 'qnbr' => $qnbr, 'quote' => $quote, 'qnotes_qt' => $qnotes]);
				$page->body .= $config->twig->render('quotes/quote/notes/note-modal.twig', ['page' => $page, 'qnbr' => $qnbr, 'qnotes' => $qnotes]);
				$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
				$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));

				$page->search_notesURL = $pages->get('pw_template=msa-noce-ajax')->url;
				$page->body .= $config->twig->render('msa/noce/ajax/notes-modal.twig');
				$page->js   .= $config->twig->render('msa/noce/ajax/js.twig', ['page' => $page]);
			} else {
				$page->headline = "Quote #$qnbr could not be found";
				$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->headline, 'msg' => "Check if the Quote Number"]);
			}
		} else {
			$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
		}
	}

	include __DIR__ . "/basic-page.php";
