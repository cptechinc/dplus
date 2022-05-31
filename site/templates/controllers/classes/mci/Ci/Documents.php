<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;
use Dplus\DocManagement\Copier;

class Documents extends Base {
	const JSONCODE       = '';
	const PERMISSION_CIO = '';

	private static $docfinder;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'folder|text', 'document|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if ($data->folder && $data->document) {
			$docm = self::getDocFinder();
			$file = $docm->getDocumentByFilename($data->folder, $data->document);
			$copier = Copier::getInstance();
			$copier->copyFile($file->getDocumentFolder()->directory, $data->document);

			if ($copier->isInDirectory($data->document)) {
				self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
			}
		}
		return self::documents($data);
	}

	private static function documents($data) {
		self::initHooks();
		self::sanitizeParametersShort($data, ['custID|text', 'folder|text']);
		$list = self::createList($data->custID);

		switch ($data->folder) {
			case 'SO':
			case 'AR':
				self::sanitizeParametersShort($data, ['ordn|ordn', 'date|text']);
				$docm = new DocFinders\SalesOrder();
				$list->title = "Sales Order #$data->ordn Documents";

				$validate = new MsoValidator();
				if ($validate->order($data->ordn)) {
					$list->returnTitle = "Sales Orders";
					$list->returnUrl = SalesOrders::ordersUrl($data->custID);
				}

				if ($validate->invoice($data->ordn)) {
					$list->returnTitle = "Sales History";
					$list->returnUrl = SalesHistory::historyUrl($data->custID, $data->date);
				}

				$list->documents = $docm->countDocuments($data->ordn) ? $docm->getDocuments($data->ordn) : [];
				break;
			case 'QT':
				self::sanitizeParametersShort($data, ['qnbr|qnbr']);
				$docm = new DocFinders\Qt();
				$list->title = "Quote #$data->qnbr Documents";
				$list->returnTitle = "Quotes";
				$list->documents = $docm->getDocuments($data->qnbr);
				$list->returnUrl = Quotes::quotesUrl($data->custID);
				break;
			case 'PAY':
				self::sanitizeParametersShort($data, ['invnbr|text', 'checknbr|text']);
				$docm = new DocFinders\Ar();
				$list->title = "Payments on Invoice #$data->invnbr Documents";
				$list->returnTitle = "Payments";
				$list->documents = $docm->getDocumentsPayment($data->invnbr, $data->checknbr);
				$list->returnUrl = Payments::paymentsUrl($data->custID);
				break;
			default:
				$docm = new DocFinders\Cu();
				$list->returnTitle = "Customer";
				$list->documents = $docm->getDocuments($data->custID);
				$list->returnUrl = self::ciUrl($data->custID);
				break;
		}
		self::pw('page')->headline = "CI: $data->custID Documents";
		return self::displayDocuments($data, $list);
	}


/* =============================================================
	URLs
============================================================= */
	public static function documentsUrl($custID, $folder = '', $document = '') {
		$url = new Purl(self::ciDocumentsUrl($custID));

		if ($folder) {
			$url->query->set('folder', $folder);
			if ($document) {
				$url->query->set('document', $document);
			}
		}
		return $url->getUrl();
	}

	public static function documentsUrlSalesorder($custID, $ordn, $date = '') {
		$url = new Purl(self::documentsUrl($custID, 'AR'));
		$url->query->set('ordn', $ordn);
		if ($date) {
			$url->query->set('date', $date);
		}
		return $url->getUrl();
	}

	public static function documentsUrlQuote($itemID, $qnbr) {
		$url = new Purl(self::documentsUrl($itemID, 'QT'));
		$url->query->set('qnbr', $qnbr);
		return $url->getUrl();
	}

	public static function documentsUrlPurchaseorder($itemID, $ponbr) {
		$url = new Purl(self::documentsUrl($custID, 'PO'));
		$url->query->set('ponbr', $ponbr);
		return $url->getUrl();
	}


	public static function documentsUrlPayment($custID, $invnbr, $checknbr) {
		$url = new Purl(self::documentsUrl($custID, 'PAY'));
		$url->query->set('invnbr', $invnbr);
		$url->query->set('checknbr', $checknbr);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayDocuments($data, WireData $list) {
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::pw('config')->twig->render('customers/ci/documents/display.twig', ['customer' => self::getCustomer($data->custID), 'list' => $list]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentUrl', function($event) {
			$page     = $event->object;
			$custID   = $event->arguments(0);
			$folder   = $event->arguments(1);
			$document = $event->arguments(2);
			$event->return = self::documentsUrl($custID, $folder, $document);
		});
	}

	private static function createList($custID) {
		$list = new WireData();
		$list->custid = $custID;
		$list->title = '';
		$list->documents = [];
		$list->returnUrl = '';
		$list->returnTitle = '';
		return $list;
	}

	public static function getDocFinder() {
		return new DocFinders\Finder();
	}
}
