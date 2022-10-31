<?php namespace Controllers\Mci\Ci\Contacts;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use CustomerQuery, Customer;
use CustomerShiptoQuery, CustomerShipto;
// Dpluso Model
use CustindexQuery, Custindex;
// ProcessWire
use ProcessWire\WireData;
use ProcessWire\Wire404Exception;
// Dplus Validators
use Dplus\CodeValidators\Mar as MarValidator;
// Dplus Filters
use Dplus\Filters;
// Dplus CRUD
use Dplus\Mci\Ci\Contact\Edit as ContactCRUD;
// Mvc Controllers
use Controllers\Mci\Ci\Shipto;

class Edit extends Contact {
	const TITLE      = 'CI: Edit Contact';
	const SUMMARY    = 'Edit Customer Contact';

/* =============================================================
	Indexes
============================================================= */
	public static function handleCRUD(WireData $data) {
		$fields = ['rid|int', 'custID|string', 'shiptoID|text', 'contactID|text', 'name|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$data->custID = self::getCustidByRid($data->rid);
		$url = self::ciContactEditUrl($data->rid, $data->shiptoID, $data->contactID);

		if (empty($data->action) === false) {
			$crud = new ContactCRUD();
			$crud->processInput(self::pw('input'));
			if ($data->name != '' && $data->contactID != $data->name) {
				$url = self::ciContactEditUrl($data->rid, $data->shiptoID, $data->name);
			}
		}
		self::pw('session')->redirect($url, $http301=false);
	}

/* =============================================================
	Displays
============================================================= */

/* =============================================================
	HTML Rendering
============================================================= */
	protected static function renderContact(WireData $data, Custindex $contact) {
		$customer = self::getCustomerByRid($data->rid);
		return self::pw('config')->twig->render('customers/ci/.new/contact-edit/display.twig', ['contact' => $contact, 'customer' => $customer]);
	}
}
