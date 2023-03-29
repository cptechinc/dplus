<?php namespace Dplus\Msa;
// Dplus Models
use SysLoginRoleQuery, SysLoginRole;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class Lrole extends WireData {
	const MODEL              = 'SysLoginRole';
	const MODEL_KEY          = 'id';
	const TABLE              = 'sys_login_role';
	const DESCRIPTION        = 'Lrole';
	const RESPONSE_TEMPLATE  = 'User Role {id} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'lrole';

	const FIELD_ATTRIBUTES = [
		'id'           => ['type' => 'text', 'maxlength' => 6],
		'description'  => ['type' => 'text', 'maxlength' => 40],
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

	/**
	 * Return JSON array for Role
	 * @param  SysLoginRole $role
	 * @return array
	 */
	public function roleJson(SysLoginRole $role) {
		return [
			'id'          => $role->id,
			'description' => $role->description,
		];
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
	 * @return SysLoginRoleQuery
	 */
	public function query() {
		return SysLoginRoleQuery::create();
	}

	/**
	 * Return Query Filtered to User ID
	 * @param  string $id    User ID
	 * @return SysLoginRoleQuery
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
	 * Return User Role
	 * @param  string $id    User ID
	 * @return SysLoginRole
	 */
	public function role($id) {
		$q = $this->queryId($id);
		return $q->findOne();
	}

	/**
	 * Return new SysLoginRole
	 * @param  string $id    User ID
	 * @return SysLoginRole
	 */
	public function new($id) {
		$role = new SysLoginRole();
		if ($id != 'new') {
			$role->setId($id);
		}
		return $role;
	}

	/**
	 * Return New or Existing SysLoginRole
	 * @param  string $id    User ID
	 * @return SysLoginRole
	 */
	public function getOrCreate($id) {
		if ($this->exists($id) === false) {
			return $this->new($id);
		}
		return $this->user($id);
	}

	/**
	 * Return Role Description
	 * @param  string $id
	 * @return bool
	 */
	public function description($id) {
		return $this->queryId($id)->select(SysLoginRole::aliasproperty('description'))->findOne();
	}
}
