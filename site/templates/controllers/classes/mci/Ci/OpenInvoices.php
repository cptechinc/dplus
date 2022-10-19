<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\OpenInvoices as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;


class OpenInvoices extends Subfunction {
	const PERMISSION_CIO = 'openinvoices';
	const JSONCODE       = 'ci-openinvoices';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|string', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::openInvoicesUrl($data->custID), $http301 = false);
		}
		return self::invoices($data);
	}

	private static function invoices($data) {
		self::getData($data);
		self::pw('page')->headline = "CI: $data->custID Open Invoices";
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayInvoices($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['custID|string']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');


		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['custid'] != $data->custID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::openInvoicesUrl($data->custID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ci', 'quotes') > 3) {
			return false;
		}
		$session->setFor('ci', 'quotes', ($session->getFor('ci', 'quotes') + 1));
		$session->redirect(self::openInvoicesUrl($data->custID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function displayInvoices($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Quotes File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl   = self::openInvoicesUrl($data->custID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer  = self::getCustomer($data->custID);
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		self::initHooks();
		return $config->twig->render('customers/ci/open-invoices/display.twig', ['customer' => $customer, 'json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function openInvoicesUrl($custID, $refreshdata = false) {
		$url = new Purl(self::ciOpenInvoicesUrl($custID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['custID|string', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['CIOPENINV', "CUSTID=$vars->custID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	private static function getDocFinder() {
		return new DocFinders\SalesOrder();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlSalesorder($event->arguments(0), $event->arguments(1));
		});
	}
}
