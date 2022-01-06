<?php namespace Controllers\Mvi\Vi;
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
		$fields = ['vendorID|text', 'folder|text', 'document|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateVendorid($data->vendorID) === false) {
			self::pw('session')->redirect(self::viUrl(), $http301 = false);
		}

		if (self::validateVendoridPermission($data) === false) {
			return self::displayInvalidVendorOrPermissions($data);
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
		self::sanitizeParametersShort($data, ['vendorID|text', 'folder|text']);
		$list = self::createList($data->vendorID);

		switch ($data->folder) {
			case 'PO':
				self::sanitizeParametersShort($data, ['ponbr|text']);
				$docm = new DocFinders\PurchaseOrder();
				$list->title = "Purchase Order #$data->ponbr Documents";
				$list->returnTitle = "Purchase Orders";
				$list->documents = $docm->getDocumentsPo($data->ponbr);
				$list->returnUrl = PurchaseOrders::ordersUrl($data->vendorID);
				break;
			case 'AP':
				self::sanitizeParametersShort($data, ['invnbr|text']);
				$docm = new DocFinders\ApInvoice();
				$list->title = "AP Invoice #$data->invnbr Documents";
				$list->returnTitle = "Purchase History";
				$list->documents = $docm->getDocuments($data->invnbr);
				$list->returnUrl = PurchaseHistory::historyUrl($data->vendorID);
				break;
		}
		self::pw('page')->headline = "VI: $data->vendorID Documents";
		return self::displayDocuments($data, $list);
	}

/* =============================================================
	URLs
============================================================= */
	public static function documentsUrl($vendorID, $folder = '', $document = '') {
		$url = new Purl(self::viDocumentsUrl($vendorID));

		if ($folder) {
			$url->query->set('folder', $folder);
			if ($document) {
				$url->query->set('document', $document);
			}
		}
		return $url->getUrl();
	}

	public static function documentsUrlPo($vendorID, $ponbr) {
		$url = new Purl(self::documentsUrl($vendorID, 'PO'));
		$url->query->set('ponbr', $ponbr);
		return $url->getUrl();
	}

	public static function documentsUrlAp($vendorID, $invnbr) {
		$url = new Purl(self::documentsUrl($vendorID, 'AP'));
		$url->query->set('invnbr', $invnbr);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayDocuments($data, WireData $list) {
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::pw('config')->twig->render('vendors/vi/documents/display.twig', ['vendor' => self::getVendor($data->vendorID), 'list' => $list]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

		$m->addHook('Page(pw_template=vi)::documentUrl', function($event) {
			$page     = $event->object;
			$vendorID = $event->arguments(0);
			$folder   = $event->arguments(1);
			$document = $event->arguments(2);
			$event->return = self::documentsUrl($vendorID, $folder, $document);
		});
	}

	private static function createList($vendorID) {
		$list = new WireData();
		$list->vendorid = $vendorID;
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
