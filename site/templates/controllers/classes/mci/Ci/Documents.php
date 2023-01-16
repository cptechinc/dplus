<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\Page;
use ProcessWire\WireData;
// Dplus Mso
use Dplus\Mso\So as Mso;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;
use Dplus\DocManagement\Copier;

/**
 * Ci\Documents
 * Handles Sales Order Documents
 */
class Documents extends AbstractSubfunctionController {

/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'folder|text', 'document|text'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		self::decorateInputDataWithCustid($data);
		self::decoratePageWithCustid($data);

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

	private static function documents(WireData $data) {
		self::initHooks();
		self::addPageData($data);
		$list = self::createList($data->custID);
		$customer = self::getCustomerByRid($data->rid);

		switch ($data->folder) {
			case 'SO':
			case 'AR':
				self::pw('page')->headline = "CI: Documents for Order #$data->ordn";
				return self::documentsSales($data, $customer, $list);
				break;
			case 'QT':
				self::pw('page')->headline = "CI: Documents for Quote #$data->qnbr";
				return self::documentsQuotes($data, $customer, $list);
				break;
			case 'PAY':
				self::pw('page')->headline = "CI: Documents for Invoice #$data->Invnbr";
				return self::documentsPayments($data, $customer, $list);
				break;
			default:
				self::pw('page')->headline = "CI: $data->custID Documents";
				return self::documentsCustomer($data, $customer, $list);
				break;
		}
	}

	private static function documentsSales(WireData $data, Customer $customer, WireData $list) {
		self::sanitizeParametersShort($data, ['ordn|ordn', 'date|text']);
		$docm = new DocFinders\SalesOrder();
		$list->title = "Sales Order #$data->ordn Documents";
		
		if (Mso\SalesOrder::instance()->exists($data->ordn)) {
			$list->returnTitle = "Sales Orders";
			$list->returnUrl = SalesOrders::ordersUrl($data->rid);
		}

		if (Mso\SalesHistory::instance()->exists($data->ordn)) {
			$list->returnTitle = "Sales History";
			$list->returnUrl = SalesHistory::historyUrl($data->rid, $data->date);
		}
		$list->documents = $docm->countDocuments($data->ordn) ? $docm->getDocuments($data->ordn) : [];
		return self::displayDocuments($data, $customer, $list);
	} 

	private static function documentsQuotes(WireData $data, Customer $customer, WireData $list) {
		self::sanitizeParametersShort($data, ['qnbr|qnbr']);
		$docm = new DocFinders\Qt();
		$list->title = "Quote #$data->qnbr Documents";
		$list->returnTitle = "Quotes";
		$list->documents = $docm->getDocuments($data->qnbr);
		$list->returnUrl = Quotes::quotesUrl($data->rid);
		return self::displayDocuments($data, $customer, $list);
	} 

	private static function documentsPayments(WireData $data, Customer $customer, WireData $list) {
		self::sanitizeParametersShort($data, ['invnbr|text', 'checknbr|text']);
		$docm = new DocFinders\Ar();
		$list->title = "Payments on Invoice #$data->invnbr Documents";
		$list->returnTitle = "Payments";
		$list->documents = $docm->getDocumentsPayment($data->invnbr, $data->checknbr);
		$list->returnUrl = Payments::paymentsUrl($data->rid);
		return self::displayDocuments($data, $customer, $list);
	} 

	private static function documentsCustomer(WireData $data, Customer $customer, WireData $list) {
		$docm = new DocFinders\Cu();
		$list->returnTitle = "Customer";
		$list->documents = $docm->getDocuments($data->custID);
		$list->returnUrl = self::ciUrl($data->rid);
		return self::displayDocuments($data, $customer, $list);
	} 

/* =============================================================
	2. Validations
============================================================= */
	

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */

/* =============================================================
	4. URLs
============================================================= */
	public static function documentsUrl($rID, $folder = '', $document = '') {
		$url = new Purl(self::ciDocumentsUrl($rID));

		if ($folder) {
			$url->query->set('folder', $folder);
			if ($document) {
				$url->query->set('document', $document);
			}
		}
		return $url->getUrl();
	}

	public static function documentsUrlSalesorder($rID, $ordn, $date = '') {
		$url = new Purl(self::documentsUrl($rID, 'AR'));
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

	public static function documentsUrlPurchaseorder($rID, $ponbr) {
		$url = new Purl(self::documentsUrl($rID, 'PO'));
		$url->query->set('ponbr', $ponbr);
		return $url->getUrl();
	}


	public static function documentsUrlPayment($rID, $invnbr, $checknbr) {
		$url = new Purl(self::documentsUrl($rID, 'PAY'));
		$url->query->set('invnbr', $invnbr);
		$url->query->set('checknbr', $checknbr);
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	private static function displayDocuments(WireData $data, Customer $customer, WireData $list) {
		$html = '';
		$html .= self::renderDocuments($data, $customer, $list);
		return $html;
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	private static function renderDocuments(WireData $data, Customer $customer, WireData $list) {
		return self::pw('config')->twig->render('customers/ci/.new/documents/display.twig', ['customer' => $customer, 'list' => $list]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */
	public static function getDocFinder() {
		return new DocFinders\Finder();
	}
	
/* =============================================================
	8. Supplemental
============================================================= */
	private static function createList($custID) {
		$list = new WireData();
		$list->custid = $custID;
		$list->title = '';
		$list->documents = [];
		$list->returnUrl = '';
		$list->returnTitle = '';
		return $list;
	}

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentUrl', function($event) {
			$rID      = $event->arguments(0);
			$folder   = $event->arguments(1);
			$document = $event->arguments(2);
			$event->return = self::documentsUrl($rID, $folder, $document);
		});
	}

	/**
	 * Decorate Page with extra Properties
	 * @param  WireData  $data
	 * @param  Page|null $page
	 * @return void
	 */
	protected static function addPageData(WireData $data, Page $page = null) {
		$page = $page ? $page : self::pw('page');
		$page->subfunctionDesc = static::TITLE;
	}
}
