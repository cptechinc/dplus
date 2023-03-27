<?php namespace Controllers\Mqo\Quote;
// Propel ORM
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use QuoteQuery, Quote as QuoteModel;
// ProcessWire
use ProcessWire\Module as PwModule;

class Notes extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['qnbr|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}

		if ($data->action) {
			self::handleCRUD($data);
		}

		if (empty($data->qnbr)) {
			return self::lookupScreen($data);
		}
		return self::qt($data);
	}

	public static function handleCRUD($data) {
		$qnotes = self::pw('modules')->get('QnotesQuote');
		$qnotes->process_input(self::pw('input'));
		self::pw('session')->redirect(self::quoteNotesUrl($data->qnbr), $http301 = false);
	}

	public static function qt($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|text']);
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}

		if ($validate->quoteAccess($data->qnbr, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}

		self::pw('page')->headline = "Quote #$data->qnbr Notes";
		return self::notes($data);
	}

	public static function notes($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|text']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}

		$session = self::pw('session');
		$html = '';

		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}

		$quote = QuoteQuery::create()->filterByQuoteid($data->qnbr)->findOne();
		$qnotes = self::pw('modules')->get('QnotesQuote');
		self::notesJs($data);
		return self::notesDisplay($data, $quote, $qnotes);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function lookupScreen($data) {
		self::pw('page')->headline = "Quote Notes";
		return parent::lookupScreen($data);
	}

	private static function notesDisplay($data, QuoteModel $quote, PwModule $qnotes) {
		$session = self::pw('session');
		$config  = self::pw('config');
		$html = '';
		$html .= self::breadCrumbs();

		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}
		$html .= $config->twig->render('quotes/quote/notes/qnotes-page.twig', ['qnbr' => $data->qnbr, 'quote' => $quote, 'qnotes_qt' => $qnotes]);
		$html .= $config->twig->render('quotes/quote/notes/note-modal.twig', ['qnbr' => $data->qnbr, 'qnotes' => $qnotes]);
		$html .= $config->twig->render('msa/noce/ajax/notes-modal.twig');
		return $html;
	}

	private static function notesJs($data) {
		$config  = self::pw('config');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/quotes/quote-notes.js'));
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		self::pw('page')->js   .= $config->twig->render('msa/noce/ajax/js.twig');
	}
}
