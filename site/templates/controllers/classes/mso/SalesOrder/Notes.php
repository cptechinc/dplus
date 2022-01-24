<?php namespace Controllers\Mso\SalesOrder;
// Propel ORM
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use SalesOrderQuery, SalesHistoryQuery;
// ProcessWire
use ProcessWire\Module as PwModule;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Notes extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ordn|text', 'document|text', 'folder|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			return self::lookupScreen($data);
		}
		return self::so($data);
	}

	public static function handleCRUD($data) {
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');
		$qnotes->process_input(self::pw('input'));
		self::pw('session')->redirect(self::orderNotesUrl($data->ordn), $http301 = false);
	}

	public static function so($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		$page->headline = "Sales Order #$data->ordn Notes";

		if ($validate->invoice($data->ordn) || $validate->order($data->ordn)) {
			return self::notes($data);
		}
	}

	public static function notes($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page     = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		/** @var ActiveRecordInterface **/
		$order = SalesOrderQuery::create()->findOneByOrdernumber($data->ordn);
		/** @var PwModule **/
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');

		if ($validate->invoice($data->ordn)) {
			/** @var ActiveRecordInterface **/
			$order = SalesHistoryQuery::create()->findOneByOrdernumber($data->ordn);
			/** @var PwModule **/
			$qnotes = self::pw('modules')->get('QnotesSalesHistory');
		}
		self::notesJs($data, $qnotes);
		return self::notesDisplay($data, $order, $qnotes);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function lookupScreen($data) {
		self::pw('page')->headline = "Sales Order Notes";
		return parent::lookupScreen($data);
	}

	private static function notesDisplay($data, ActiveRecordInterface $order, PwModule $qnotes) {
		$session = self::pw('session');
		$config  = self::pw('config');
		$html = '';


		$html .= self::breadCrumbs();

		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}
		$html .= $config->twig->render('sales-orders/sales-order/notes/page.twig', ['user' => self::pw('user'), 'ordn' => $data->ordn, 'order' => $order, 'qnotes_so' => $qnotes]);
		$html .= $config->twig->render('sales-orders/sales-order/notes/modal.twig', ['ordn' => $data->ordn, 'qnotes_so' => $qnotes]);
		$html .= $config->twig->render('msa/noce/ajax/notes-modal.twig');
		return $html;
	}

	private static function notesJs($data, PwModule $qnotes) {
		$page    = self::pw('page');
		$config  = self::pw('config');
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		$page->js .= $config->twig->render('sales-orders/sales-order/notes/js.twig', ['ordn' => $data->ordn, 'qnotes' => $qnotes]);
		$page->js .= $config->twig->render('msa/noce/ajax/js.twig');
	}
}
