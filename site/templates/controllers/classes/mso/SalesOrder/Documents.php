<?php namespace Controllers\Mso\SalesOrder;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Model
use DocumentQuery, Document;
// Dplus CodeValidators
use Dplus\CodeValidators\Mso as MsoValidator;
// Dplus Document Finders
use Dplus\DocManagement\Finders\SalesOrder as DocumentsSo;
use Dplus\DocManagement\Copier;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Documents extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ordn|text', 'document|text', 'folder|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			return self::lookupScreen($data);
		}

		if ($data->document && $data->folder) {
			/** @var DocumentsSo **/
			$docm = self::docm();
			$file = $docm->getDocumentByFilename($data->folder, $data->document);
			$copier = Copier::getInstance();
			$copier->copyFile($file->getDocumentFolder()->directory, $data->document);

			if ($copier->isInDirectory($data->document)) {
				self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
			}
		}
		return self::so($data);
	}

	public static function so($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		/** @var MsoValidator **/
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		$page->headline = "Sales Order #$data->ordn Documents";

		if ($validate->invoice($data->ordn) || $validate->order($data->ordn)) {
			return self::documents($data);
		}
	}

	public static function documents($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		/** @var MsoValidator **/
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		/** @var DocumentsSo **/
		$docm      = self::docm();
		$documents = $docm->getDocuments($data->ordn);
		return self::documentsDisplay($data, $documents);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function lookupScreen($data) {
		self::pw('page')->headline = "Sales Order Documents";
		return parent::lookupScreen($data);
	}

	private static function documentsDisplay($data, ObjectCollection $documents) {
		$html  = self::breadCrumbs();
		$html .= self::pw('config')->twig->render('sales-orders/sales-order/documents.twig', ['documents' => $documents]);
		return $html;
	}
}
