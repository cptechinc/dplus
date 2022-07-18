<?php namespace Dplus\Mci\Ci\Contact;
// Dpluso Models
use CustindexQuery, Custindex;
use UseractionsQuery, Useractions;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;

// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class Edit extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return CustindexQuery
	 */
	public function query() {
		return CustindexQuery::create();
	}

	/**
	 * Return Query filtered By Custid, Shiptoid, Contact
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Ship-to ID
	 * @param  string $contactID  Contact ID
	 * @return CustindexQuery
	 */
	public function queryContact($custID, $shiptoID, $contactID) {
		$q = $this->query();
		$q->filterByCustid($custID)->filterByShiptoid($shiptoID)->filterByContact($contactID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return Contact
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Ship-to ID
	 * @param  string $contactID  Contact ID
	 * @return Custindex
	 */
	public function contact($custID, $shiptoID, $contactID) {
		$q = $this->queryContact($custID, $shiptoID, $contactID);
		return $q->findOne();
	}

	/**
	 * Return If Contact Exists
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Ship-to ID
	 * @param  string $contactID  Contact ID
	 * @return bool
	 */
	public function exists($custID, $shiptoID, $contactID) {
		$q = $this->queryContact($custID, $shiptoID, $contactID);
		return boolval($q->count());
	}

	/**
	 * Return new Contact
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Ship-to ID
	 * @param  string $contactID  Contact ID
	 * @return Custindex
	 */
	public function new($custID, $shiptoID, $contactID) {
		$c = new Custindex();
		$c->setCustid($custID);
		$c->setShiptoid($shiptoID);
		if (strtolower($contactID) == 'new') {
			$c->setContactid($contactID);
		}
		return $c;
	}

	/**
	 * Return new or Existing Contact
	 * @param  string $custID     Customer ID
	 * @param  string $shiptoID   Ship-to ID
	 * @param  string $contactID  Contact ID
	 * @return Custindex
	 */
	public function getCreate($custID, $shiptoID, $contactID) {
		if ($this->exists($custID, $shiptoID, $contactID)) {
			return $this->contact($custID, $shiptoID, $contactID);
		}
		return $this->new($custID, $shiptoID, $contactID);
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Process Input, call function
	 * @param  WireInput $input Input
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update-contact':
				$this->inputUpdate($input);
				break;
			default:
				$itemID = $values->text('itemID');
				$message = self::DESCRIPTION_RECORD . " ($itemID) was not saved, no action was specified";
				$response = ItmResponse::response_error($itemID, $message);
				$this->wire('session')->setFor('response', 'itm', $response);
				break;
		}
	}

	/**
	 * Update Contact Record from Input Data
	 * @param  WireInput $input  Input Data
	 * @return void
	 */
	private function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$newcontactID = '';

		$contact = $this->inputUpdateContactFields($input);

		if ($values->text('contactID') != $values->text('name')) {
			$contact->setContact($values->text('name'));
			$newcontactID = $values->text('name');
		}
		$saved = $contact->save();
		if (boolval($saved)) {
			$this->updateUserActionsContact($input, $contact);
			$this->requestUpdateContact($contact, $newcontactID);
		}
	}

	/**
	 * Update Contact Record Fields
	 * @param  WireInput $input               [description]
	 * @return void
	 */
	private function inputUpdateContactFields(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$contact = $this->contact($values->text('custID'), $values->text('shiptoID'), $values->text('contactID'));
		$contact->setTitle($values->text('title'));
		$contact->setPhone($values->text('officephone'));
		$contact->setExtension($values->text('extension'));
		$contact->setFaxnbr($values->text('fax'));
		$contact->setCellphone($values->text('cellphone'));
		$contact->setEmail($values->text('email'));
		$contact->setArcontact($values->yn('arcontact'));
		$contact->setDunningcontact($values->yn('duncontact'));
		$contact->setBuyingcontact($values->yn('buycontact'));
		$contact->setCertcontact($values->yn('certcontact'));
		$contact->setAckcontact($values->yn('ackcontact'));
		$contact->setDummy('P');
		$contact->setDate(date('Ymd'));
		$contact->setTime(date('His'));
		return $contact;
	}

	/**
	 * Update the contact on the User Actions to the new Contact ID if needed
	 * @param  WireInput $input   Input Data
	 * @param  Custindex $contact Customer Contact
	 * @return void
	 */
	private function updateUserActionsContact(WireInput $input, Custindex $contact) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('contactID') == $values->text('name')) {
			return false;
		}

		$q = UseractionsQuery::create();
		$q->filterByCustomerlink($contact->custid);
		if (empty($contact->shiptoid) === false) {
			$q->filterByShiptolink($contact->shiptoid);
		}
		$q->filterByContactlink($values->text('contactID'));

		if (boolval($q->count()) === false) {
			return true;
		}
		return $q->update(array('Contactlink' => $values->text('name')));
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Request to Cobol
	 * @param  array  $data
	 * @return void
	 */
	private function requestDplus(array $data) {
		$config  = $this->wire('config');
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['default'], $this->sessionID);
	}

	/**
	 * Request Update Contact from Dplus
	 * @param  Custindex $contact
	 * @param  string    $newcontactID
	 * @return void
	 */
	public function requestUpdateContact(Custindex $contact, $newcontactID = '') {
		$data = ['EDITCONTACT', "CUSTID=$contact->custid", "SHIPID=$contact->shiptoid", "CONTACT=$contact->contact"];
		if ($newcontactID != '' && $contact->contact != $newcontactID) {
			$data[] = "OLDCONTACT=$contact->contact";
			$data[] = "NEWCONTACT=$newcontactID";
		} else {
			$data[] = "OLDCONTACT=";
			$data[] = "NEWCONTACT=";
		}
		$this->requestDplus($data);
	}
}
