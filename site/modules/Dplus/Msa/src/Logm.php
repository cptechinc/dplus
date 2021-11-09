<?php namespace Dplus\Msa;
// Dplus Models
use DplusUserQuery, DplusUser;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class Logm extends WireData {
	const MODEL              = 'DplusUser';
	const MODEL_KEY          = 'id';
	const TABLE              = 'syslogin';
	const DESCRIPTION        = 'Logm';
	const RESPONSE_TEMPLATE  = 'Logm User {id} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'logm';

	const FIELD_ATTRIBUTES = [
		'id'        => ['type' => 'text', 'maxlength' => DplusUser::LENGTH_USERID],
		'name'      => ['type' => 'text', 'maxlength' => 20],
		'companyid' => ['type' => 'text', 'maxlength' => 3],
	];

	public function __construct() {
		$this->sessionID = session_id();

		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($field) || empty($attr)) {
			return false;
		}
		if (array_key_exists($field, static::FIELD_ATTRIBUTES) === false) {
			return false;
		}
		if (array_key_exists($attr, static::FIELD_ATTRIBUTES[$field]) === false) {
			return false;
		}
		return static::FIELD_ATTRIBUTES[$field][$attr];
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return DplusUserQuery
	 */
	public function query() {
		return DplusUserQuery::create();
	}

	/**
	 * Return Query Filtered to User ID
	 * @param  string $id    User ID
	 * @return DplusUserQuery
	 */
	public function queryId($id) {
		$q = $this->query();
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Return if User ID Exists
	 * @param  string $id    User ID
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->queryId($id);
		return boolval($q->count());
	}

	/**
	 * Return User
	 * @param  string $id    User ID
	 * @return DplusUser
	 */
	public function user($id) {
		$q = $this->queryId($id);
		return $q->findOne();
	}

	/**
	 * Return new DplusUser
	 * @param  string $id    User ID
	 * @return DplusUser
	 */
	public function new($id) {
		$opt = new DplusUser();
		$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		if ($id != 'new') {
			$opt->setId($id);
		}
		return $opt;
	}

	/**
	 * Return JSON array for User
	 * @param  DplusUser $user
	 * @return array
	 */
	public function userJson(DplusUser $user) {
		return [
			'id'    => $user->id,
			'name'  => $user->name,
			'email' => $code->email
		];
	}
}