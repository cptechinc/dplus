<?php namespace ProcessWire;

trait ScreenFormatterTraits {

	/**
	 * Array of Fields by section, and their properties
	 * NOTE JSON DECODED ARRAY
	 * @var string
	 */
	protected $fields;

	/**
	 * Formatter
	 * NOTE JSON DECODED ARRAY
	 * @var string
	 */
	protected $formatter = false;

	/**
	 * Array with the structure of Screen
	 * @var array
	 */
	protected $tableblueprint = false; // WILL BE ARRAY

	/**
	 * Where is formatted derived from
	 * ex. input | database | default
	 * @var string
	 */
	protected $source;

	protected $userID;

	/**
	 * Date Types for Formatter Selection
	 * @var string
	 */
	static DATE_OPTIONS = array(
		'm/d/y' => 'MM/DD/YY',
		'm/d/Y' => 'MM/DD/YYYY',
		'm/d' => 'MM/DD',
		'm/Y' => 'MM/YYYY'
	);

	/**
	 * Returns an array with default formatting
	 * @return array an array with default formatting
	 */
	public function get_defaultformattercolumn() {
		return array(
			"line"           => 0,
			"column"         => 0,
			"type"           => "C",
			"col-length"     => 0,
			"label"          => "Label",
			"before-decimal" => false,
			"after-decimal"  => false,
			"date-format"    => false,
			"percent"        => false,
			"input"          => false,
			"data-justify"   => "left",
			"label-justify"  => "right"
		);
	}

	public function init() {
		parent::init();
		$this->load_fields();
		$this->load_formatter();
		$this->generate_tableblueprint();
	}

	public function load_fields() {
		$this->fields = json_decode(file_get_contents($this->directory_fields), true);
	}

	/**
	 * Returns the Fields Definition and loads them if not already defined
	 * @return array fields
	 */
	public function get_fields() {
		if (!$this->fields) {
			$this->load_fields();
		}
		return $this->fields;
	}

	/**
	* Returns the formatter property
	* @return array Formatter
	*/
	public function get_formatter() {
		if (!$this->formatter) {
			$this->load_formatter();
		}
		return $this->formatter;
	}

	/**
	 * Sets the formatter array with the field definitions
	 * 1. Checks if user has a formatter
	 * 2. Checks if there's a saved default formatter
	 * 3. Get default formatter
	 * @return void
	 */
	protected function load_formatter() {
		if ($this->does_formatterexist($this->userID, $this->code)) {
			$this->formatter = $this->load_dbformatter($this->userID, $this->code);
			$this->source = 'database';
		} else {
			$this->formatter = file_get_contents(__DIR__."/$this->code.json");
			$this->source = 'default';
		}
		$this->formatter = json_decode($this->formatter, true);
	}

	/**
	 * Loads formatter from Database
	 * @var string JSON Encoded Formatter
	 */
	protected load_dbformatter($userID, $formatter) {
		$q = TableformatterQuery::create()
		$q->filterByUser($userID);
		$q->filterByFormattertype($formatter);
		$q->select('data');
		return $q->count()->findOne();
	}

	/**
	 * Returns if user has a formatter for this type saved
	 * @return bool             Does User have a formatter
	 */
	protected function does_formatterexist($userID, $formatter) {
		$q = TableformatterQuery::create();
		$q->filterByUser($userID);
		$q->filterByFormattertype($formatter);
		return boolval($q->count());
	}

	/**
	 * Returns blueprint and loads it if need be
	 * @return array blueprint array
	 */
	public function get_tableblueprint() {
		if (!$this->tableblueprint) {
			$this->generate_tableblueprint();
		}
		return $this->tableblueprint;
	}

	/**
	 * Parses through the formatter array and sets the tableblueprint
	 * @return void
	 */
	protected function generate_tableblueprint() {
		$tablesections = array_keys($this->fields);
		$table = array('colcount' => $this->formatter['colcount']);

		foreach ($tablesections as $section) {
			$columns = array_keys($this->formatter[$section]['columns']);

			$table[$section] = array(
				'rowcount' => $this->formatter[$section]['rowcount'],
				'colcount' => 0,
				'rows' => array()
			);

			for ($i = 1; $i < $this->formatter[$section]['rows'] + 1; $i++) {
				$table[$section]['rows'][$i] = array('columns' => array());

				foreach ($columns as $column) {
					if ($this->formatter[$section]['columns'][$column]['line'] == $i) {
						$col = array(
							'id'             => $column,
							'label'          => $this->formatter[$section]['columns'][$column]['label'],
							'column'         => $this->formatter[$section]['columns'][$column]['column'],
							'type'           => $this->fields[$section][$column],
							'col-length'     => $this->formatter[$section]['columns'][$column]['col-length'],
							'before-decimal' => $this->formatter[$section]['columns'][$column]['before-decimal'],
							'after-decimal'  => $this->formatter[$section]['columns'][$column]['after-decimal'],
							'date-format'    => $this->formatter[$section]['columns'][$column]['date-format'],
							'percent'        => $this->formatter[$section]['columns'][$column]['percent'],
							'input'          => $this->formatter[$section]['columns'][$column]['input'],
							'data-justify'   => $this->formatter[$section]['columns'][$column]['data-justify'],
							'label-justify'  => $this->formatter[$section]['columns'][$column]['label-justify'],
						 );
						$table[$section]['rows'][$i]['columns'][$this->formatter[$section]['columns'][$column]['column']] = $col;
						$table[$section]['colcount'] = $col['column'] > $table[$section]['colcount'] ? $col['column'] : $table[$section]['colcount'];
					}
				}
			}
		}
		$this->tableblueprint = $table;
	}
}
