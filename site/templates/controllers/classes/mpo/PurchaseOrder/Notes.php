<?php namespace Controllers\Mpo\PurchaseOrder;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Model
use DocumentQuery, Document;
use PurchaseOrderQuery, PurchaseOrder;
// Dplus CodeValidators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Dplus Document Finders
use Dplus\DocManagement\Finders\PurchaseOrder as DocumentsPo;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Notes extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ponbr|text', 'document|text', 'folder|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ponbr)) {
			return self::lookupScreen($data);
		}

		if ($data->document && $data->folder) {
			/** @var DocumentsPo **/
			$docm = self::docm();
			$docm->moveDocument($data->folder, $data->document);
			self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
		}
		return self::po($data);
	}

	public static function po($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr']);
		/** @var MpoValidator **/
		$validate = self::validator();

		if ($validate->po($data->ponbr) === false) {
			return self::invalidSo($data);
		}
		self::pw('page')->headline = "Purchase Order #$data->ponbr Notes";

		if ($validate->po($data->ponbr)) {
			return self::notes($data);
		}
	}

	public static function notes($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr']);
		$page = self::pw('page');
		$config   = self::pw('config');
		/** @var MpoValidator **/
		$validate = self::validator();

		if ($validate->po($data->ponbr) === false) {
			return self::invalidPo($data);
		}

		$po = PurchaseOrderQuery::create()->findOneByPonbr($data->ponbr);
		self::qnotesJs();
		$config->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		return self::qnotesDisplay($data, $po);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function lookupScreen($data) {
		self::pw('page')->headline = "Purchase Order Documents";
		return parent::lookupScreen($data);
	}

	private static function qnotesDisplay($data, PurchaseOrder $po) {
		/** @var DocumentsPo **/
		$qnotes = self::qnotes();
		$html   = self::breadCrumbs();
		$html   .= self::pw('config')->twig->render('purchase-orders/purchase-order/qnotes/qnotes.twig', ['ponbr' => $data->ponbr, 'purchaseorder' => $po, 'qnotes' => $qnotes]);
		return $html;
	}

	public static function qnotesJs() {
		self::pw('page')->js .= self::pw('config')->twig->render('msa/noce/ajax/js.twig');
		self::pw('page')->js .= self::pw('config')->twig->render('purchase-orders/purchase-order/qnotes/js.twig');
	}
}
