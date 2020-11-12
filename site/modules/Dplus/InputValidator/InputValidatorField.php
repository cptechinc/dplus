<?php namespace ProcessWire;

/**
 * InputValidatorField
 *
 * Module for InputValidator options for an field
 *
 * @property string $key         Input Key  NOTE: CAN be used as the property / validator function values
 * @property string $description Description of Property / Input
 * @property string $input       Input Name NOTE: Can be left blank, uses $key
 * @property string $function    Validator function to use for validation
 * @property string $sanitizer   Sanitizer function to use for Input Sanitization
 * @property int    $length      Max Length of field property 0 = no max length
 * @property string $allowblank  Allow Value to be blank?
 * @property array  $requires    Array of input names ( NOT BLANK ) this requires
 *
 */
class InputValidatorField extends WireData {

	const EXAMPLE_FIELDS = [
		'packgroup' => array( // KEY CAN BE USED AS PROPERTY / FUNCTION
			'function'    => 'hazmat_packgroup', // IF ABSENT / EMPTY WILL DEFAULT TO KEY'S VALUE
			'description' => 'Packing Group',
			'input'       => 'packgroup', //
			'allow_blank' => true,
			'requires'    => ['number']
		),
	];

	public function __construct() {
		$this->key = '';
		$this->input = '';
		$this->property = '';
		$this->function = '';
		$this->sanitizer = 'text';
		$this->length = 0;

		$this->description = '';
		$this->allow_blank = true;
		$this->requires = [];
	}

	/**
	 * Return Record Property name
	 * @return string
	 */
	public function property() {
		return $this->property ? $this->property : $this->key;
	}

	/**
	 * Return Validator Function name
	 * @return string
	 */
	public function function() {
		return $this->function ? $this->function : $this->key;
	}

	/**
	 * Return Input name
	 * @return string
	 */
	public function input() {
		return $this->input ? $this->input : $this->key;
	}

	/**
	 * Return Sanitizer Function name
	 * @return string
	 */
	public function sanitizer() {
		return $this->sanitizer ? $this->sanitizer : 'text';
	}

	public function allow_blank() {
		return boolval($this->allow_blank);
	}

/* =============================================================
	Static Constructor Functions
============================================================= */
	/**
	 * Return Instance from array
	 * @param  string $key   Key e.g itemID
	 * @param  array  $field Field with properties see self::EXAMPLE_FIELDS[0]
	 * @return InputValidatorField
	 */
	public static function from_array($key, $field) {
		$f = new self();
		$f->key = $key;

		foreach ($field as $key => $value) {
			$f->$key = $value;
		}
		return $f;
	}

	/**
	 * Return Multiple instances from Array with Keys
	 * @param  array  $fields Field with properties see self::EXAMPLE_FIELDS
	 * @return InputValidatorField[]
	 */
	public static function multiple_from_array(array $fields) {
		$validatorfields = [];

		foreach ($fields as $key => $field) {
			$validatorfields[] = self::from_array($key, $field);
		}
		return $validatorfields;
	}
}

class InputValidatorFieldArray extends WireArray {
	/**
	 * Return Multiple instances from Array with Keys
	 * @param  array  $fields Field with properties see self::EXAMPLE_FIELDS
	 * @return InputValidatorField[]
	 */
	public static function from_array(array $fields) {
		$wireArray = new self();

		foreach ($fields as $key => $field) {
			$wireArray->set($key, InputValidatorField::from_array($key, $field));
		}
		return $wireArray;
	}
}
