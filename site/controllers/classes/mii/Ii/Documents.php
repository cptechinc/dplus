<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Controllers\Mii\IiFunction;

class Documents extends IiFunction {
	const JSONCODE       = '';
	const PERMISSION_IIO = '';

/* =============================================================
	1. Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'folder|text', 'document|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if ($data->folder && $data->document) {
			$docm = self::pw('modules')->get('DocumentManagementIi');
			$docm->move_document($data->folder, $data->document);

			if ($docm->is_filewebaccessible($data->document)) {
				self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
			}
		}
		return self::documents($data);
	}

	public static function documents($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		$fields = ['itemID|text'];
		self::sanitizeParametersShort($data, $fields);

		$page    = self::pw('page');
		$page->headline = "II: $data->itemID Documents";
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::display($data);
		return $html;
	}

/* =============================================================
	2. Data Requests
============================================================= */
	# NONE

/* =============================================================
	3. URLs
============================================================= */
	public static function documentsUrl($itemID, $folder = '', $document = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->path->add('documents');
		$url->query->set('itemID', $itemID);

		if ($folder) {
			$url->query->set('folder', $folder);
			if ($document) {
				$url->query->set('document', $document);
			}
		}
		return $url->getUrl();
	}

	public static function documentsUrlQuote($itemID, $qnbr) {
		$url = new Purl(self::documentsUrl($itemID, 'QT'));
		$url->query->set('qnbr', $qnbr);
		return $url->getUrl();
	}

	public static function documentsUrlApInvoice($itemID, $invnbr, $date = '') {
		$url = new Purl(self::documentsUrl($itemID, 'AP'));
		$url->query->set('invnbr', $invnbr);
		if ($date) {
			$url->query->set('date', $date);
		}
		return $url->getUrl();
	}

	public static function documentsUrlSalesorder($itemID, $ordn, $date = '') {
		$url = new Purl(self::documentsUrl($itemID, 'AR'));
		$url->query->set('ordn', $ordn);
		if ($date) {
			$url->query->set('date', $date);
		}
		return $url->getUrl();
	}

	public static function documentsUrlPurchaseorder($itemID, $ponbr) {
		$url = new Purl(self::documentsUrl($itemID, 'PO'));
		$url->query->set('ponbr', $ponbr);
		return $url->getUrl();
	}


/* =============================================================
	4. Data Retrieval
============================================================= */
	# NONE
/* =============================================================
	5. Displays
============================================================= */
	private static function display($data) {
		self::init();
		self::sanitizeParametersShort($data, ['itemID|text', 'folder|text']);
		$list = self::createList($data->itemID);

		$docm = self::pw('modules')->get('DocumentManagementIi');

		switch ($data->folder) {
			case 'SO':
			case 'AR':
				self::sanitizeParametersShort($data, ['ordn|ordn', 'date|text']);
				$docm = self::pw('modules')->get('DocumentManagementSo');
				$list->title = "Sales Order #$data->ordn Documents";

				$validate = new MsoValidator();
				if ($validate->order($data->ordn)) {
					$list->returnTitle = "Sales Orders";
					$list->returnUrl = SalesOrders::ordersUrl($data->itemID);
				}

				if ($validate->invoice($data->ordn)) {
					$list->returnTitle = "Sales History";
					$list->returnUrl = SalesHistory::historyUrl($data->itemID, $data->date);
				}

				$list->documents = $docm->count_documents($data->ordn) ? $docm->get_documents($data->ordn) : [];
				break;
			case 'ACT': // Item Activity
				self::sanitizeParametersShort($data, ['type|text', 'reference|text']);
				$list->title = "$data->type $data->reference Documents";
				$list->returnTitle = "Activity";
				$list->documents = $docm->get_documents_activity($data->type, $data->reference);
				$list->returnUrl = Activity::activityUrl($data->itemID);
				break;
			case 'QT':
				self::sanitizeParametersShort($data, ['qnbr|qnbr']);
				$docm = self::pw('modules')->get('DocumentManagementQt');
				$list->title = "Quote #$data->qnbr Documents";
				$list->returnTitle = "Quotes";
				$list->documents = $docm->get_documents($data->qnbr);
				$list->returnUrl = Quotes::quotesUrl($data->itemID);
				break;
			case 'AP':
				self::sanitizeParametersShort($data, ['invnbr|ponbr', 'date|text']);
				$docm = self::pw('modules')->get('DocumentManagementPo');
				$list->title = "AP Invoice #$data->invnbr Documents";
				$list->returnTitle = "AP Invoices";
				$list->documents = $docm->get_documents_invoice($data->invnbr);
				$list->returnUrl = PurchaseHistory::historyUrl($data->itemID, $data->date);
				break;
			case 'PO':
				self::sanitizeParametersShort($data, ['ponbr|ponbr']);
				$docm = self::pw('modules')->get('DocumentManagementPo');
				$list->title = "Purchase Order #$data->ponbr Documents";
				$list->returnTitle = "Purchase Orders";
				$list->documents = $docm->get_documents_po($data->ponbr);
				$list->returnUrl = PurchaseOrders::ordersUrl($data->itemID);
				break;
			default:
				$list->title = "";
				$list->returnTitle = "";
				$list->documents = $docm->get_documents_item($data->itemID);
				break;
		}

		return self::pw('config')->twig->render('items/ii/documents/display.twig', ['item' => self::getItmItem($data->itemID), 'list' => $list]);
	}

/* =============================================================
	6. Supplements
============================================================= */
	public static function init() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=ii-item)::documentUrl', function($event) {
			$page     = $event->object;
			$itemID   = $event->arguments(0);
			$folder   = $event->arguments(1);
			$document = $event->arguments(2);
			$event->return = self::documentsUrl($itemID, $folder, $document);
		});
	}

	private static function createList($itemID) {
		$list = new WireData();
		$list->itemid = $itemID;
		$list->title = '';
		$list->documents = [];
		$list->returnUrl = '';
		$list->returnTitle = '';
		return $list;
	}
}
