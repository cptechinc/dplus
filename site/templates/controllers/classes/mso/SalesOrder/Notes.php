<?php namespace Controllers\Mso\SalesOrder;
// Dplus Model
use SalesOrderQuery, SalesHistoryQuery;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Notes extends Base {

	public static function index($data) {
		$fields = ['ordn|text', 'document|text', 'folder|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			return self::invalidSo($data);
		}
		return self::so($data);
	}

	public static function so($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
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
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		$session = self::pw('session');
		$html = '';

		if ($session->response_qnote) {
			$html .= $config->twig->render('code-tables/code-table-response.twig', ['response' => $session->response_qnote]);
			$session->remove('response_qnote');
		}

		$order = SalesOrderQuery::create()->findOneByOrdernumber($data->ordn);
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');

		if ($validate->invoice($data->ordn)) {
			$order = SalesHistoryQuery::create()->findOneByOrdernumber($data->ordn);
			$qnotes = self::pw('modules')->get('QnotesSalesHistory');
		}
		$page->search_notesURL = self::pw('pages')->get('pw_template=msa-noce-ajax')->url;
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		$html .= $config->twig->render('sales-orders/sales-order/notes/qnotes-page.twig', ['user' => self::pw('user'), 'ordn' => $data->ordn, 'order' => $order, 'qnotes_so' => $qnotes]);
		$html .= $config->twig->render('sales-orders/sales-order/notes/note-modal.twig', ['ordn' => $data->ordn, 'qnotes_so' => $qnotes]);
		$html .= $config->twig->render('msa/noce/ajax/notes-modal.twig', []);
		$page->js .= $config->twig->render('sales-orders/sales-order/notes/js.twig', ['ordn' => $data->ordn, 'qnotes' => $qnotes]);
		$page->js .= $config->twig->render('msa/noce/ajax/js.twig');
		return $html;
	}
}
