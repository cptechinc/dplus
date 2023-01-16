<?php namespace Dplus\Qnotes;
// Dolus Models
use ItemXrefCustomerNote;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;

class Icxm extends Qnotes {
	const MODEL                = 'ItemXrefCustomerNote';
	const MODEL_KEY            = 'id';
	const MODEL_TABLE          = 'notes_whse_invc_stmt';
	const DESCRIPTION          = 'CXM Item Notes';
	const RESPONSE_TEMPLATE    = '{custID} Order Notes for {itemID} was {not} {crud}';
	const TYPE                 = 'ICXM';
	const TYPE_DESCRIPTION     = 'Item/Customer X-Ref';
	const FIELD_ATTRIBUTES = [
		'itemid' => ['type' => 'text'],
		'custid' => ['type' => 'text'],
		'note'   => ['type' => 'text', 'cols' => 35],
		'quote'  => ['type' => 'text', 'label' => 'Quote', 'options' => ['N' => 'No', 'Y' => 'Yes'], 'default' => 'N'],
		'pick'   => ['type' => 'text', 'label' => 'Pick', 'options' => ['N' => 'No', 'Y' => 'Yes'], 'default' => 'N'],
		'pack'   => ['type' => 'text', 'label' => 'Pack', 'options' => ['N' => 'No', 'Y' => 'Yes'], 'default' => 'N'],
		'invoice'          => ['type' => 'text', 'label' => 'Invoice', 'options' => ['N' => 'No', 'Y' => 'Yes'], 'default' => 'N'],
		'acknowledgement'  => ['type' => 'text', 'label' => 'Acknowledgement', 'options' => ['N' => 'No', 'Y' => 'Yes'], 'default' => 'N'],
	];
	const FIELDS_FORMS = ['quote', 'pick', 'pack', 'invoice', 'acknowledgement'];

	protected static $instance;


/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Initialize Field Attributes
	 * NOTE: values may be set from configs
	 * @return void
	 */
	public function initFieldAttributes() {
		$this->fieldAttributes = static::FIELD_ATTRIBUTES;
		$configAR = Configs\Ar::config();
		$this->fieldAttributes['note']['cols'] = $configAR->columns_notes_invoice;

		$configQT = Configs\Qt::config();
		$this->fieldAttributes['quote']['default']   = $configQT->note_default_quote;
		$this->fieldAttributes['pick']['default']    = $configQT->note_default_pick;
		$this->fieldAttributes['pack']['default']    = $configQT->note_default_pack;
		$this->fieldAttributes['invoice']['default'] = $configQT->note_default_invoice;
		$this->fieldAttributes['acknowledgement']['default'] = $configQT->note_default_acknowledgement;
	}

	/**
	 * Return Record Description
	 * @return string
	 */
	public function dbDescription() {
		$desc = static::TYPE_DESCRIPTION;
		return "$desc Notes";
	}

	/**
	 * Return Record Description
	 * @return string
	 */
	public function typeDescription() {
		return static::TYPE_DESCRIPTION;
	}


/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @return bool
	 */
	public function notesExist($itemID, $custID){
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByCustid($custID);
		return boolval($q->count());
	}

	/**
	 * Returns if Notes Exist
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @param  string $form    Forms Code
	 * @return bool
	 */
	public function notesExistForm($itemID, $custID, $form){
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByCustid($custID);
		$q->filterByForm($form);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @param  string $form    Forms Code
	 * @return array
	 */
	public function getNotesArray($itemID, $custID, $form) {
		$q = $this->query();
		$q->select(ItemXrefCustomerNote::aliasproperty('note'));
		$q->filterByItemid($itemID);
		$q->filterByCustid($custID);
		$q->filterByForm($form);
		return $q->find()->toArray();
	}

	/**
	 * Returns Line 1 of Every Note
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @return SalesOrderNotes[]|ObjectCollection
	 */
	public function getNotesSummarized($itemID, $custID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByCustid($custID);
		$q->filterBySequence(1);
		return $q->find();
	}

	/**
	 * Return Note Line
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @param  string $form    Forms Code
	 * @param  int    $line    Line Number
	 * @return ItemXrefCustomerNote
	 */
	public function noteLine($itemID, $custID, $form = '', $line = 1) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByCustid($custID);
		$q->filterBySequence($line);
		if ($form) {
			$q->filterByForm($form);
		}
		return $q->findOne();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New ItemXrefCustomerNote
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @return ItemXrefCustomerNote
	 */
	public function new($itemID, $custID) {
		$note = ItemXrefCustomerNote::new();
		$note->setItemid($itemID);
		$note->setCustid($custID);
		$note->setQuote($this->fieldAttribute('quote', 'default'));
		$note->setPick($this->fieldAttribute('pick', 'default'));
		$note->setPack($this->fieldAttribute('pack', 'default'));
		$note->setInvoice($this->fieldAttribute('invoice', 'default'));
		$note->setAcknowledgement($this->fieldAttribute('acknowedgement', 'default'));
		$note->generateForm(); // PK
		$note->setSequence(1); // PK
		$note->setDummy('P');
		return $note;
	}

/* =============================================================
	CRUD Delete Functions
============================================================= */
	/**
	 * Delete Notes
	 * @param  string $itemID  Item ID (Internal / Our Item ID)
	 * @param  string $custID  Cust ID
	 * @return bool
	 */
	public function deleteNotes($itemID, $custID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByCustid($custID);

		if ($q->count() === 0) {
			return true;
		}
		return $q->delete();
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Write Notes from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function _inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->string('itemID');
		$custID = $values->string('custID');
		$this->deleteNotes($itemID, $custID);

		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
		$savedLines = [];

		foreach ($noteLines as $key => $line) {
			$sequence = $key + 1;
			$note = $this->new($itemID, $custID);
			$note->setQuote($values->yn('quote'));
			$note->setPick($values->yn('pick'));
			$note->setPack($values->yn('pack'));
			$note->setInvoice($values->yn('invoice'));
			$note->setAcknowledgement($values->yn('acknowledgement'));
			$note->generateKey2(); // PK
			$note->setSequence($sequence); // PK
			$note->setNote($line);
			$note->setDate(date('Ymd'));
			$note->setTime(date('His').'00');
			$savedLines[$sequence] = boolval($note->save());
		}
		$response = $this->updateAndRespond($note, $savedLines);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Process Written Lines, update Dplus cobol
	 * @param  ItemXrefCustomerNote $note
	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	 * @return Response
	 */
	private function updateAndRespond(ItemXrefCustomerNote $note, array $savedLines = []) {
		$response = new Response();
		$response->setKey("$note->itemid|$note->custid");
		$response->setAction(Response::CRUD_UPDATE);

		if (in_array(false, $savedLines)) {
			$errorLines =
			array_filter($savedLines, function($value, $key) {
				return  $value == false;
			}, ARRAY_FILTER_USE_BOTH);

			$response->addMsgReplacement('{lines}', implode(", ", array_keys($errorLines)));

			if (sizeof($errorLines)) {
				$response->setError(true);
			}
		} else {
			$response->setSuccess(true);
			$response->addMsgReplacement('{itemID}', $note->itemid);
			$response->addMsgReplacement('{custID}', $note->custid);
		}
		$response->buildMessage(static::RESPONSE_TEMPLATE);

		if ($response->hasSuccess()) {
			$this->updateDplus($note);
		}
		return $response;
	}

	/**
	 * Delete Notes from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function _inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->string('itemID');
		$custID = $values->string('custID');

		$note = $this->new($itemID, $custID);
		$response = $this->deleteAndRespond($note);

		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete note, update Dplus cobol
	 * @param  ItemXrefCustomerNote $note
	 * @return Response
	 */
	protected function deleteAndRespond(ItemXrefCustomerNote $note) {
		$success = $this->deleteNotes($note->itemid, $note->custid);

		$response = new Response();
		$response->setKey("$note->itemid|$note->custid");
		$response->setAction(Response::CRUD_DELETE);
		$response->setSuccess($success);
		$response->setError($success === false);
		$response->addMsgReplacement('{lines}', '');
		$response->addMsgReplacement('{itemID}', $note->itemid);
		$response->addMsgReplacement('{custID}', $note->custid);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($note);
		}
		return $response;
	}
}
