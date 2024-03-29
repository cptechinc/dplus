<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Controllers
use Controllers\Mii\Ii;
use ItemMasterItem;

/**
 * Ci\Pricing
 * 
 * Handles the CI Pricing Page
 */
class Pricing extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'pricing';
	const TITLE      = 'Pricing';
	const SUMMARY    = 'View Customer Pricing';
	const JSONCODE   = 'ii-pricing';
	const SUBFUNCTIONKEY = 'pricing';
	
/* =============================================================
	1. Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'custID|string', 'itemID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);
		self::decorateInputDataWithCustid($data);
		self::decoratePageWithCustid($data);

		if (empty($data->itemID)) {
			return self::selectItem($data);
		}

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			$id = self::pw('config')->ci->useRid ? $data->rid : $data->custID;
			self::pw('session')->redirect(self::pricingUrl($id, $data->itemID), $http301 = false);
		}
		return self::pricing($data);
	}

	private static function selectItem(WireData $data) {
		$customer = self::getCustomerFromWireData($data);

		self::pw('page')->custid   = $data->custID;
		self::pw('page')->headline = "CI: $customer->name Pricing";
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/ajax-modal.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl(self::jsPath() . 'select-item.js'));
		return self::displaySelectItem($data);
	}

	private static function pricing(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerFromWireData($data);
		self::pw('page')->custid   = $customer->id;
		self::pw('page')->headline = "CI: $customer->name Pricing for $data->itemID";

		$html = '';
		$html .= self::displayPricing($data, $customer, $json);
		return $html;
	}

/* =============================================================
	2. Validations
============================================================= */

/* =============================================================
	3. Data Fetching / Requests / Retrieval
============================================================= */
	/**
	 * Return URL to Fetch Data
	 * @param  WireData $data
	 * @return string
	 */
	protected static function fetchDataRedirectUrl(WireData $data) {
		$id = self::pw('config')->ci->useRid ? $data->rid : $data->custID;
		return self::pricingUrl($id, $data->itemID, $refresh=true);
	}

	/**
	 * Return if JSON Data matches for this Customer ID
	 * @param  WireData $data
	 * @param  array    $json
	 * @return bool
	 */
	protected static function validateJsonFileMatches(WireData $data, array $json) {
		return $json['itemid'] == $data->itemID && $json['custid'] == $data->custID;
	}

	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'custID|string', 'itemID|text', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		self::decorateInputDataWithCustid($data);
		return ['CIPRICE', "ITEMID=$data->itemID", "CUSTID=$data->custID"];
	}

/* =============================================================
	4. URLs
============================================================= */
	public static function pricingUrl($custID, $itemID = '', $refreshdata = false) {
		$url = new Purl(self::ciPricingUrl($custID));

		if ($itemID) {
			$url->query->set('itemID', $itemID);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	5. Displays
============================================================= */
	private static function displaySelectItem(WireData $data) {
		return self::renderSelectItem($data);
	}

	private static function displayPricing(WireData $data, Customer $customer, $json = []) {
		self::addPageData($data);
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Pricing');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		$item = Ii\Pricing::getItmItem($data->itemID);
		return self::renderPricing($data, $customer, $item, $json);
	}

/* =============================================================
	6. HTML Rendering
============================================================= */
	private static function renderSelectItem(WireData $data) {
		return self::pw('config')->twig->render('customers/ci/.new/pricing/select-item/display.twig');
	}

	private static function renderPricing(WireData $data, Customer $customer, ItemMasterItem $item, array $json) {
		return self::pw('config')->twig->render('customers/ci/.new/pricing/display.twig', ['item' => $item, 'customer' => $customer, 'json' => $json]);
	}

/* =============================================================
	7. Class / Module Getting
============================================================= */

/* =============================================================
	8. Supplemental
============================================================= */

/* =============================================================
	9. Hooks / Object Decorating
============================================================= */
}
