<?php namespace Dplus\ScreenFormatters;

use ProcessWire\WireData, ProcessWire\WireInput;

use TableformatterQuery, Tableformatter;

/**
 * Class for Formatting Screens
 */
class ScreenFormatter extends WireData {

	/**
	 * Array of Fields by section, and their properties
	 * NOTE JSON DECODED ARRAY
	 * @var array
	 */
	protected $fields;

	/**
	 * Formatter
	 * NOTE JSON DECODED ARRAY
	 * @var array
	 */
	protected $formatter = false;

	/**
	 * Array with the structure of Screen
	 * @var array
	 */
	protected $tableblueprint = false;

	/**
	 * Where is formatted derived from
	 * ex. input | database | default
	 * @var string
	 */
	protected $source;

	/**
	 * User ID
	 * @var string
	 */
	protected $userID = 'default';

	/**
	 * Screen Formatter Code
	 * @var string
	 */
	protected $code = '';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array();

	const COLUMNS_TRACKING = array('Tracking Number');

	const COLUMNS_PHONE = array('phone', 'fax');

	/**
	 * Date Types for Formatter Selection
	 * @var string
	 */
	const DATE_TYPES = array(
		'm/d/y' => 'MM/DD/YY',
		'm/d/Y' => 'MM/DD/YYYY',
		'm/d' => 'MM/DD',
		'm/Y' => 'MM/YYYY'
	);

	/**
	 * ProcessWire Init
	 *
	 * @return void
	 */
	public function init() {
		$this->data['directory_fields'] = $this->wire('config')->paths->siteModules.'Dplus/JsonDataFiles/fields/';
	}

	/**
	 * Initializes formatter
	 * @return void
	 */
	public function init_formatter() {
		$this->init();
		$this->load_fields();
		$this->load_formatter();
		$this->generate_tableblueprint();
	}

	/**
	 * Sets User ID
	 * @param string $userID Dplus User ID
	 */
	public function set_userID($userID) {
		$this->userID = $userID;
	}

	/**
	 * Return Formatter User ID
	 * @return string Dplus User ID
	 */
	public function get_userID() {
		return $this->userID;
	}

	/**
	 * Return Array of Date Types
	 * @return array
	 */
	public function get_datetypes() {
		return self::DATE_TYPES;
	}

	/**
	 * Return Sections of Formatter
	 * @return array  e.g array('header', 'detail')
	 */
	public function get_datasections() {
		return $this->datasections;
	}

	/**
	 * Returns Source of Formatter
	 * @return string
	 */
	public function get_source() {
		return $this->source;
	}

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

	/**
	 * Loads the Fields
	 * @return void
	 */
	public function load_fields() {
		$this->fields = json_decode(file_get_contents($this->directory_fields."$this->code.json"), true);
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
		} if ($this->does_formatterexist('default', $this->code)) {
			$this->formatter = $this->load_dbformatter('default', $this->code);
			$this->source = 'database-default';
		} else {
			$this->formatter = file_get_contents(__DIR__."/../default/$this->code.json");
			$this->source = 'default';
		}
		$this->formatter = json_decode($this->formatter, true);
	}

	/**
	 * Loads formatter from Database
	 * @var string JSON Encoded Formatter
	 */
	protected function load_dbformatter($userID, $formatter) {
		$q = TableformatterQuery::create()->filterByUserFormattertype($userID, $formatter);
		$q->select('data');
		return $q->findOne();
	}

	/**
	 * Returns if user has a formatter for this type saved
	 * @return bool             Does User have a formatter
	 */
	public function does_formatterexist($userID, $formatter) {
		$q = TableformatterQuery::create();
		$q->filterByUser($userID);
		$q->filterByFormattertype($formatter);
		return boolval($q->count());
	}

	/**
	 * Saves Formatter to Database
	 * Creates / Updates Formatter
	 * @return bool
	 */
	public function save() {
		if ($this->does_formatterexist($this->userID, $this->code)) {
			$q = TableformatterQuery::create();
			$q->filterByUser($this->userID);
			$q->filterByFormattertype($this->code);
			$formatter = $q->findOne();
		} else {
			$formatter = new Tableformatter();
			$formatter->setUser($this->userID);
			$formatter->setformatterType($this->code);
		}
		$formatter->setData(json_encode($this->formatter));
		return $formatter->save();
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
	 * Generates and Sets tableblueprint property based on
	 * the input
	 * @param  WireInput $input Input with field definitions
	 * @return void
	 */
	public function generate_formatterfrominput(WireInput $input) {
		$this->formatter = false;
		$postarray = $table = array('colcount' => 0);
		$tablesections = array_keys($this->fields);

		if ($input->post->user) {
			$userID = $input->post->text('user');
			$this->set_userID($userID);
		} else {
			$this->set_userID($this->wire('user')->loginid);
		}

		foreach ($tablesections as $tablesection) {
			$postarray[$tablesection] = array('rows' => 0, 'colcount' => 0, 'columns' => array());
			$table[$tablesection] = array('rowcount' => 0, 'rows' => array());

			foreach (array_keys($this->fields[$tablesection]) as $column) {
				$postcolumn = str_replace(' ', '', $column);
				$linenumber = $input->post->int($postcolumn.'-line-'.$tablesection);
				$length = $input->post->int($postcolumn.'-length-'.$tablesection);
				$colnumber = $input->post->int($postcolumn.'-column-'.$tablesection);
				$label = $input->post->text($postcolumn.'-label-'.$tablesection);
				$dateformat = $beforedecimal = $afterdecimal = false;
				$justify_data = $input->post->text($postcolumn.'-data-justify-'.$tablesection);
				$justify_label = $input->post->text($postcolumn.'-label-justify-'.$tablesection);
				$is_input = $input->post->text($postcolumn.'-is-input-'.$tablesection);
				$is_percent = $input->post->text($postcolumn.'-is-percent-'.$tablesection);

				if ($this->fields[$tablesection][$column]['type'] == 'D') {
					$dateformat = $input->post->text($postcolumn.'-date-format-'.$tablesection);
				} elseif ($this->fields[$tablesection][$column]['type'] == 'N') {
					$beforedecimal = $input->post->int($postcolumn.'-before-decimal');
					$afterdecimal = $input->post->int($postcolumn.'-after-decimal-'.$tablesection);
				}

				$postarray[$tablesection]['columns'][$column] = array(
					'line' => $linenumber,
					'column' => $colnumber,
					'col-length' => $length,
					'label' => $label,
					'before-decimal' => $beforedecimal,
					'after-decimal' => $afterdecimal,
					'date-format' => $dateformat,
					'percent' => boolval($is_percent),
					'input' => boolval($is_input),
					'data-justify' => $justify_data,
					'label-justify' => $justify_label
				);
			}

			foreach ($postarray[$tablesection]['columns'] as $column) {
				if ($column['line'] > $postarray[$tablesection]['rows']) {
					$postarray[$tablesection]['rows'] = $column['line'];
				}
			}

			for ($i = 1; $i < ($postarray[$tablesection]['rows'] + 1); $i++) {
				$table[$tablesection]['rows'][$i] = array('columns' => array());

				foreach ($postarray[$tablesection]['columns'] as $column) {
					if ($column['line'] == $i) {
						$table[$tablesection]['rows'][$i]['columns'][$column['column']] = $column;
					}
				}
			}

			foreach ($table[$tablesection]['rows'] as $row) {
				$columncount = 0;
				$maxcolumn = 0;
				foreach ($row['columns'] as $column) {
					$columncount += $column['col-length'];
					$maxcolumn = $column['column'] > $maxcolumn ? $column['column'] : $maxcolumn;
				}
				$columncount = ($maxcolumn > $columncount) ? $maxcolumn : $columncount;
				$postarray[$tablesection]['colcount'] = $columncount;
				$postarray['colcount'] = ($columncount > $postarray['colcount']) ? $columncount : $postarray['colcount'];
			}
		}
		$this->formatter = $postarray;
		$this->source = 'input';
		$this->generate_tableblueprint();


	}

	/**
	 * Parses through the formatter array and sets the tableblueprint
	 * @return void
	 */
	protected function generate_tableblueprint() {
		$tablesections = array_keys($this->fields);
		$this->formatter['cols'] = isset($this->formatter['cols']) ? $this->formatter['cols'] : 0;
		$table = array('cols' => $this->formatter['cols']);

		foreach ($tablesections as $section) {
			$columns = array_keys($this->formatter[$section]['columns']);

			$table[$section] = array(
				'rowcount' => $this->formatter[$section]['rows'],
				'colcount' => 0,
				'rows' => array()
			);

			for ($i = 1; $i < $this->formatter[$section]['rows'] + 1; $i++) {
				$table[$section]['rows'][$i] = array('columns' => array());

				foreach ($columns as $column) {
					if (array_key_exists($column, $this->fields[$section])) {
						if ($this->formatter[$section]['columns'][$column]['line'] == $i) {
							if (!array_key_exists('percent', $this->formatter[$section]['columns'][$column])) {
								$this->formatter[$section]['columns'][$column]['percent'] = false;
							}

							if (!array_key_exists('input', $this->formatter[$section]['columns'][$column])) {
								$this->formatter[$section]['columns'][$column]['input'] = false;
							}

							$col = array(
								'id'             => $column,
								'label'          => $this->formatter[$section]['columns'][$column]['label'],
								'column'         => $this->formatter[$section]['columns'][$column]['column'],
								'type'           => $this->fields[$section][$column]['type'],
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
							$table[$section]['colcount'] += $col['col-length'] > 1 ? $col['col-length'] : 1;
							$table['cols'] = $col['column'] > $table['cols']  ? $col['column'] : $table['cols'];
						}
					}
				}
			}
			$table['cols'] = $table[$section]['colcount'] > $table['cols'] ? $table[$section]['colcount'] : $table['cols'];
		}
		$this->tableblueprint = $table;
	}

	/**
	 * Generates the celldata based of the column, column type and the json array it's in, looks at if the data is numeric
	 * @param string $parent the array in which the data is contained
	 * @param string $column the key in which we use to look up the value, may contain the type
	 */
	public function format_celldata($parent, $column, $type = '') {
		$modules = $this->wire('modules');
		$bootstrap = $modules->get('HtmlWriter');
		$celldata = '';
		$qtyregex = "/(quantity)/i";
		$type = !empty($type) ? $type : $column['type'];

		if (array_key_exists($column['id'], $parent) === false) {
			return '';
		}

		// Format data
		if ($type == 'D') {
			$celldata = (strlen($parent[$column['id']]) > 0) ? date($column['date-format'], strtotime($parent[$column['id']])) : $parent[$column['id']];
		} elseif ($type == 'N') {
			if (is_string($parent[$column['id']])) {
				$celldata = number_format(floatval($parent[$column['id']]), $column['after-decimal']);
			} else {
				$celldata = number_format($parent[$column['id']], $column['after-decimal']);
			}
			if (!empty($column['percent'])) {
				$celldata .= "%";
			}
		} else {
			if (array_key_exists($column['id'], $parent)) {
				$celldata = $parent[$column['id']];
			} else {
				$celldata = '';
			}
		}

		// Format data HTML formatting
		if (in_array($column['id'], self::COLUMNS_TRACKING)) {
			$modules_url = $modules->get('DplusURLs');
			$href = $modules_url->get_trackingURL($parent['Service Type'], $parent[$column['id']]);
			return $href ? $bootstrap->a("class=link|href=$href|target=_blank", $celldata) : $celldata;
		} elseif (in_array($column['id'], self::COLUMNS_PHONE)) {
			$modules_url = $modules->get('DplusURLs');
			$href = $modules_url->generate_phoneurl($parent[$column['id']]);
			return $bootstrap->a("class=link|href=tel:$href", $celldata);
		} elseif (preg_match($qtyregex , $column['id'])) {
			return $celldata;

			// BOTTLE CASE QTY LOGIC
			// if (DplusWire::wire('modules')->isInstalled('QtyPerCase')) {
			// 	$qtypercase = DplusWire::wire('modules')->get('QtyPerCase');
			// 	$itemID = isset($parent["Item ID"]) ? $parent["Item ID"] : DplusWire::wire('session')->itemid;
			// 	$description = $qtypercase->generate_casebottleqtydesc($itemID, $celldata);
			// 	return $bootstrap->span("class=has-hover|data-toggle=tooltip|title=$description", $celldata);
			// } else {
			// 	return $celldata;
			// }
		} elseif (!empty($column['input'])) {
			return $bootstrap->input("class=form-control input-sm underlined|value=$celldata");
		} else {
			return $celldata;
		}
	}

	/**
	 * Return the bootstrap Justify class for the code
	 * @param  string $justifycode Justify code L(eft), R(right), C(enter)
	 * @return string              text-left, text-right, text-center
	 */
	public function get_justifyclass($justifycode) {
		return $this->wire('modules')->get('JsonDataFiles')->get_justifyclass($justifycode);
	}

	/**
	 * Return the bootstrap Justify classes with codes
	 */
	public function get_justifyclasses() {
		return $this->wire('modules')->get('JsonDataFiles')->get_justifyclasses();
	}
}
