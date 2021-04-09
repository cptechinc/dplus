<?php namespace Controllers\Mii\Ii;
// Purl\Url
use Purl\Url as Purl;
// ProcessWire
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
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

		self::pw('modules')->get('DpagesMii')->init_iipage();
		return self::documents($data);
	}

	public static function documents($data) {
		if (self::validateItemidPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}
		self::pw('modules')->get('DpagesMii')->init_iipage();
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

/* =============================================================
	4. Data Retrieval
============================================================= */
	# NONE
/* =============================================================
	5. Displays
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
	protected static function display($data) {
		self::init();
		self::sanitizeParametersShort($data, ['itemID|text', 'folder|text']);
		$list = self::createList($data->itemID);

		$docm = self::pw('modules')->get('DocumentManagementIi');

		switch ($data->folder) {
			case 'SO':
			case 'AR':
				self::sanitizeParametersShort($data, ['ordn|ordn']);
				$docm = self::pw('modules')->get('DocumentManagementSo');
				$list->title = "Sales Order #$data->ordn Documents";
				$list->returnTitle = "Sales Orders";
				$list->returnUrl = self::pw('pages')->get('pw_template=ii-sales-orders')->url."?itemID=$data->itemID";
				$list->documents = $docm->count_documents($data->ordn) ? $docm->get_documents($data->ordn) : [];
				break;
			case 'ACT': // Item Activity
				self::sanitizeParametersShort($data, ['type|text', 'reference|text']);
				$list->title = "$data->type $data->reference Documents";
				$list->documents = $docm->get_documents_activity($data->type, $data->reference);

				$list->returnUrl = Activity::activityUrl($data->itemID);
				break;
			case 'QT':

				break;
			case 'AP':

				break;
			case 'PO':

				break;
		}

		return self::pw('config')->twig->render('items/ii/documents/display.twig', ['item' => self::getItmItem($data->itemID), 'list' => $list]);
	}
}
