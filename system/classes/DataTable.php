<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Data Table Class ******
* This class provides the methods necessary to edit data in an SQL table.
* 
****** Example of Use ******

// Prepare Data Table based on a desired table in a database
$dataTable = new DataTable($database['name'], "users");

// List the Columns that you want to manage in this Data Table.
// There are multiple column settings to choose from: "select", "fuzzyTime"
$dataTable->setColumns("id", "username", "email", ["hair_color" => "select"], ["date_joined" => 'fuzzyTime']);

// Provide Rules about your Data Table
$dataTable->setPage($data->page);
$dataTable->setLimit($data->limit);
$dataTable->addFilter("date_joined", ">", time() - 3600);
$dataTable->addFilter("confirmed", "=", 1);
$dataTable->addSort("date_joined", "desc");

// Acquire the Data Table Schema
// This is needed for $dataTable->view and $dataTable->autoForm
$dataTable->getSchema();

// Run Actions Done to the Data Table (delete, edit, sorting, etc)
$dataTable->runActions();

// Prepare a form to create new entries
$form = new Form($dataTable->table);

// Form Validation
if($form->success())
{
	// Process the data
	$dataTable->autoProcess(); // Processes automatically with the Data Table rules
}

// Display the Table
$dataTable->view();

// Display Table Pagination
$dataTable->pagination();

// Build the form using the Data Table automation
$dataTable->autoForm($form);

****** Methods Available ******
* __construct($database, $table)
* 
* DataTable::setColumns($columns)		// Set which columns get called
* DataTable::setLimit($limit)			// Sets the number of listings per view
* DataTable::setPage($page)				// Sets the current page to view
* 
* DataTable::params(['page' => 2])		// Returns the base Query String with changes that you specify. 
* 
* DataTable::addFilter($column, $value)				// Filters the results by your value
* DataTable::addSort($column, $sortType = "asc")	// Sets the next sort type (can have multiples)
* 
* DataTable::runQuery()					// Returns the table query
* DataTable::rowCount()					// Sets the row count (based on parameters provided up to now)
* DataTable::delete($id)				// Deletes a row from the DataTable based on the ID given
* 
* DataTable::getSchema()				// Gets the current Schema
* DataTable::getSelectList($column)		// Gets a selection list from the column
* 
* DataTable::runActions()				// Run actions done to the Data Table (delete, edit, sort, etc)
* DataTable::view()						// Generates a table view
* DataTable::pagination()				// Generates pagination links
* DataTable::autoForm()					// Generates a form to create new entries
* DataTable::autoProcess()				// Automatically processes a submitted form
*/

class DataTable {
	
	
/****** Prepare Variables ******/
	public $urlPath = "";
	public $urlParameters = array();
	
	public $database = "";
	public $table = "";
	
	public $currentPage = 1;
	public $maxPages = 1;
	public $whereString = "";
	public $whereArray = array();
	public $orderBy = "";
	public $limit = 20;
	
	public $columnString = "*";
	public $columnArray = array();
	
	public $deleteColumn = "id";
	
	public $schemaPrep = array();
	public $schema = array();
	
	
/****** Constructor ******/
	function __construct
	(
		$database		/* <str> The database of the SQL table that you're working with. */,
		$table			/* <str> The name of the SQL table that you're working with. */
	)
	
	// $dataTable = new DataTable("myDatabase", "myTable");
	{
		// Prepare Values
		$this->database = Sanitize::variable($database);
		$this->table = Sanitize::variable($table);
		
		// Get URL Data
		$getPath = parse_url($_SERVER['REQUEST_URI']);
		$this->urlPath = $getPath['path'];
		
		// Prepare the Base Query String
		// Only allow certain GET values to be recorded (some are just one-shots, like delete)
		$urlArgs = array();
		
		if(isset($getPath['query']))
		{
			parse_str($getPath['query'], $urlArgs);
		}
		
		foreach($urlArgs as $key => $value)
		{
			if(in_array($key, array("page", "limit", "sortBy", "sortDir")))
			{
				$this->urlParameters[$key] = $value;
			}
		}
	}
	
	
/****** Set Shown Columns ******/
	public function setColumns
	(
		/* <ARGS...> */		/* <str> or <array> The column(s) that you're reviewing in the Data Table. */
							/* For Arrays: Key is the column, Value is the way to interpret it in the view. */
	)						/* RETURNS <void> */
	
	// $dataTable->setColumns("id", "username", "email", ["date_joined" => "fuzzyTime"]);
	{
		// Prepare Variables
		$args = func_get_args();
		$colStr = "";
		
		// Cycle through the arguments and set your variables appropriately
		foreach($args as $column)
		{
			// For columns provided as arrays:
			if(is_array($column))
			{
				$key = key($column);
				$this->columnArray[$key] = $column[$key]; 
				$colStr .= ($colStr != "" ? ", " : "") . "`" . Sanitize::variable($key) . "`";
			}
			
			// For columns provided as strings:
			else
			{
				$this->columnArray[$column] = "standard";
				$colStr .= ($colStr != "" ? ", " : "") . "`" . Sanitize::variable($column) . "`";
			}
		}
		
		$this->columnString = ($colStr != "" ? $colStr : "*");
	}
	
	
/****** Set Limit (maximum number of rows to return) ******/
	public function setLimit
	(
		$limit = 20		/* <int> The maximum number of rows to return. */
	)					/* RETURNS <void> */
	
	// $dataTable->setLimit(30);
	{
		$this->limit = (is_numeric($limit) ? $limit : 20);
	}
	
	
/****** Set Current Page ******/
	public function setPage
	(
		$page = 1		/* <int> Sets the current page of rows being listed. */
	)					/* RETURNS <void> */
	
	// $dataTable->setPage(30);
	{
		$this->currentPage = (is_numeric($page) ? $page : 1);
	}
	
	
/****** Return Parameters With Changes ******/
	public function params
	(/*
		[MULTIPLE ARGS] <array>
			[0] is the name of the parameter to switch.
			[1] is the value to switch it to.
			
	*/)				/* RETURNS <str> URL Query string with changes based on the parameters. */
	
	// $dataTable->params()											// Returns "?page=1&sortBy=user
	// $dataTable->params(['page' => 2]);							// Returns "?page=2&sortBy=user
	// $dataTable->params(['data' => 'hello']);						// Returns "?page=1&sortBy=user&data=hello
	// $dataTable->params(['page' => 4], 'sortBy' => 'login_time')	// Returns "?page=4&sortBy=login_time
	{
		$args = func_get_args();
		$cloneParams = $this->urlParameters;
		
		foreach($args as $value)
		{
			foreach($value as $key => $val)
			{
				$cloneParams[$key] = $val;
			}
		}
		
		// Prepare the Query String & return it
		$queryString = "";
		
		foreach($cloneParams as $key => $value)
		{
			$queryString .= ($queryString == "" ? "?" : "&") . $key . "=" . $value;
		}
		
		return $queryString;
	}
	
	
/****** Add Filter ******/
	public function addFilter
	(
		$column				/* <str> The column that you want to use in your filter. */,
		$comparison			/* <str> The operator to use for comparison. Can also use LIKE. */,
		$value				/* <str> The value that you'd like to filter by. */
	)						/* RETURNS <void> */
	
	// $dataTable->addFilter("age", ">", 15);
	// $dataTable->addFilter("name", "LIKE", "smith");
	{
		// Prepare Variables
		$column = "`" . Sanitize::variable($column) . "`";
		$comparison = Sanitize::whitelist(trim(strtoupper($comparison)), "=<>LIKE");
		$value = Sanitize::variable((string)$value);
		
		// Provide Matching Option
		if($comparison == "LIKE")
		{
			$value = "%" . $value . "%";
		}
		
		// Prepare the filtering values
		$this->whereString = $this->whereString . ($this->whereString != "" ? " AND " : "") . $column . " " . $comparison . " ?";
		array_push($this->whereArray, $value);
	}
	
	
/****** Add Sort-By Option ******/
	public function addSort
	(
		$column				/* <str> The column that you want to sort by. */,
		$sortType = "ASC"	/* <str> The direction you want to sort by. */
	)						/* RETURNS <void> */
	
	// $dataTable->addSort("username", "desc");
	{
		// Prepare Variables
		$column = "`" . Sanitize::variable($column) . "`";
		$sortType = (strtoupper($sortType) == "ASC" ? "ASC" : "DESC");
		
		// Prepare the filtering values
		$this->orderBy = $this->orderBy . ($this->orderBy != "" ? ", " : "") . $column . " " . $sortType;
	}
	
	
/****** Run Data Query ******/
	public function runQuery (
	)			/* RETURNS <array> The SQL data from the Data Table. */
	
	// $dataTable->runQuery();
	{
		// Prepare SQL
		$where = ($this->whereString != "" ? " WHERE " . $this->whereString : "");
		$orderBy = ($this->orderBy != "" ? " ORDER BY " . $this->orderBy : "");
		
		// Run the SQL Query
		return Database::selectMultiple("SELECT " . $this->columnString . " FROM " . $this->table . $where . $orderBy . " LIMIT " . (($this->currentPage - 1) * $this->limit) . ", " . ($this->limit + 0), $this->whereArray);
	}
	
	
/****** Delete Query ******/
	public function delete
	(
		$id 	/* <int> The ID of the row to delete. */
	)			/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// $dataTable->delete(154);
	{
		// Prepare SQL
		$where = " WHERE id=?" . ($this->whereString != "" ? " AND " . $this->whereString : "");
		$whereArray = $this->whereArray;
		array_unshift($whereArray, $id);
		
		// Run the SQL Query
		return Database::query("DELETE FROM " . $this->table . $where . " LIMIT 1", $whereArray);
	}
	
	
/****** Get the Row Count of the SQL Search ******/
	public function rowCount (
	)			/* RETURNS <int> The number of rows returned by the query. */
	
	// $dataTable->runQuery();
	{
		// Prepare SQL
		$where = ($this->whereString != "" ? " WHERE " . $this->whereString : "");
		
		// Run the SQL Query
		$data = Database::selectOne("SELECT COUNT(*) as rowCount FROM " . $this->table . $where, $this->whereArray);
		
		return (isset($data['rowCount']) ? $data['rowCount'] : 0);
	}
	
	
/****** Get Schema ******/
	public function getSchema(
	)			/* RETURNS <void> */
	
	// $dataTable->getSchema();
	{
		// Get Table Schema
		$this->schemaPrep = Database::selectMultiple("SELECT column_name, data_type, character_maximum_length, column_default FROM information_schema.columns WHERE table_schema = ? and table_name=?", array($this->database, $this->table));
		
		$this->schema = array();
		
		foreach($this->schemaPrep as $column)
		{
			if(isset($this->columnArray[$column['column_name']]))
			{
				array_push($this->schema, $column);
			}
		}
	}
	
	
/****** Get Selection List ******/
	public function getSelectList
	(
		$column		/* <str> The name of the column to return a selection list from. */
	)				/* RETURNS <void> */
	
	// $selectionList = $dataTable->getSelectList("hair_color");
	{
		$column = Sanitize::variable($column, "-");
		
		return Database::selectMultiple("SELECT `" . $column . "` FROM `" . $this->table . "` GROUP BY `" . $column . "` LIMIT 100", array($column));
	}
	
	
/****** Run Actions done to the Data Table (delete, edit, etc) ******/
	public function runActions (
	)				/* RETURNS <void> */
	
	// $dataTable->runActions();
	{
		// Get URL Data
		$getPath = parse_url($_SERVER['REQUEST_URI']);
		
		// Determine the actions run
		$urlArgs = array();
		
		if(isset($getPath['query']))
		{
			parse_str($getPath['query'], $urlArgs);
		}
		
		foreach($urlArgs as $key => $value)
		{
			switch($key)
			{
				case "delete":
					$this->delete($value);
				break;
			}
		}
	}
	
	
/****** Display the Data Table ******/
	public function view (
	)				/* RETURNS <void>, but outputs HTML of the table. */
	
	// $dataTable->view();
	{
		// Prepare the inline styling
		$html = '
		<style>
			.table { display:table; border-left: 1px solid black; border-top: 1px solid black; }
			.row-header { display: table-row; background: #cfcfcf; font-size:1.1em; font-weight:bold; }
			.row-footer { display: table-row; background: #dad9d3; font-size:1.1em; font-weight:bold; }
			.row { display: table-row; }
			.row:nth-child(odd) { background: #f2f2f2; }
			.row:nth-child(even) { background: #dbdddd; }
			.row:hover { background-color:#bbddee; }
			.cell { display:table-cell; padding:4px; border-right: 1px solid black; border-bottom: 1px solid black; }
		</style>';
		
		// Draw the Table
		$html .= '
		<div class="table">
			<div class="row-header">
				<div class="cell">&nbsp;</div>';
			
			foreach($this->schema as $column)
			{
				$html .= '
				<div class="cell">' . ucwords(str_replace("_", " ", $column['column_name'])) . '</div>';
			}
			
		$html .= '
			</div>';
		
		// Run the Query
		$sqlData = $this->runQuery();
		
		// Draw each table row
		foreach($sqlData as $row)
		{
			$html .= '
			<div class="row">
				<div class="cell">' . ($this->deleteColumn != "" ? '<a href="' . $this->urlPath . $this->params(['delete' => $row['id']]) . '"><input type="button" name="blah" value="X" style="background-color:#aa4444;color:white;" /></a>' : '&nbsp;') . '</div>';
				
				foreach($this->schema as $column)
				{
					$columnName = $column['column_name'];
					
					switch($this->columnArray[$columnName])
					{
						// If this column is shown as "Fuzzy Time", display a more human-readable time
						case "fuzzyTime":
							
							$time = (is_numeric($row[$columnName]) ? $row[$columnName] : strtotime($row[$columnName]));
							
							$html .= '
							<div class="cell">' . Time::fuzzy($time) . '</div>';
							
						break;
						
						// All other columns get entered normally
						default:
							$html .= '
							<div class="cell">' . $row[$columnName] . '</div>';
						break;
					}
				}
				
			$html .= '
			</div>';
		}
		
		$html .= '
		</div>';
		
		echo $html;
	}
	
	
/****** Display the Pagination ******/
	public function pagination (
	)				/* RETURNS <void>, but outputs HTML of the table. */
	
	// $dataTable->pagination();
	{
		// Get the maximum number of pages
		$rowCount = $this->rowCount();
		$maxPages = ceil($rowCount / $this->limit);
		
		// Get the pagination range
		$minPage = max(1, $this->currentPage - 4);
		$maxPage = max(min($minPage + 8, $maxPages), 1);
		
		// Prepare the inline styling
		$html = '
		<style>
		.table-menu
		{
			padding-top:12px;
			padding-bottom:12px;
		}
		.table-menu a
		{
			background-color:#cecece;
			border-radius:10px;
			padding:8px;
			text-decoration:none;
			color:black;
		}
		.table-menu span
		{
			background-color:#dfdfdf;
			border-radius:10px;
			padding:8px;
			text-decoration:none;
			color:white;
		}
		.table-menu .active
		{
			background-color:#000000;
		}
		</style>';
		
		// Create the pagination links
		$html .= '
		<div class="table-menu">';
		
		$html .= ($this->currentPage > 1 ? ' <a href="' . $this->urlPath . $this->params(['page' => $this->currentPage - 1]) . '">' : '<span>') . 'Previous' . ($this->currentPage > 1 ? '</a>' : '</span>');
		
		for($i = $minPage;$i <= $maxPage;$i++)
		{
			if($this->currentPage == $i)
			{
				$html .= '
				<span class="active">' . $i . '</span>';
			}
			else
			{
				$html .= '
				<a href="' . $this->urlPath . $this->params(['page' => $i]) . '">' . $i . '</a>';
			}
		}
		
		$html .= ($this->currentPage < $maxPages ? ' <a href="' . $this->urlPath . $this->params(['page' => $this->currentPage + 1]) . '">' : ' <span>') . 'Next' . ($this->currentPage < $maxPages ? '</a>' : '</span>');
		
		$html .= '
		</div>';
		
		echo $html;
	}
	
	
/****** Create DataTable New Submission Form ******/
	public function autoForm
	(
		&$form			/* <object> The form object that you want to use for auto-form generation. */,
		$toPage = ""	/* <str> The page to submit the form to. (defaults to own page) */
	)					/* RETURNS <void>, but outputs HTML of the table. */
	
	// $dataTable->autoForm($form);
	{
		// Get the Primary Key of the Table
		$result = Database::selectOne("SHOW KEYS FROM `" . $this->table . "` WHERE Key_name = 'PRIMARY'", array());
		$primaryKey = $result['Column_name'];
		
		foreach($this->schema as $value)
		{
			// Prepare Values
			$columnName = $value['column_name'];
			
			// Skip to next listing if primary key
			if($columnName == $primaryKey) { continue; }
			
			// Check the data type of the column and create the form appropriately
			switch($value['data_type'])
			{
				case "tinyint":
				case "smallint":
				case "mediumint":
				case "int":
				case "varchar":
					switch($this->columnArray[$columnName])
					{
						case "select":
							$form->select($columnName, ucwords(str_replace("_", " ", $columnName)));
							
							$selectionList = $this->getSelectList($columnName);
							
							foreach($selectionList as $selValue)
							{
								$form->selectOption($selValue[$columnName], $selValue[$columnName]);
							}
						break;
						
						default:
							$form->text($columnName, ucwords(str_replace("_", " ", $columnName)));
						break;
					}
				break;
				
				case "text":
					$form->textarea($columnName, ucwords(str_replace("_", " ", $columnName)));
				break;
			}
		}
		
		$form->submit("Submit", "submit");
		$form->generate($this->urlPath . $this->params());
	}
	
	
/****** Process Form Submission Automatically (based on the DataTable rules) ******/
	public function autoProcess (
	)				/* RETURNS <void>, but outputs HTML of the table. */
	
	// $dataTable->autoProcess();
	{
		// Prepare Values
		global $data;
		
		$valueSQL_part1 = "";
		$valueSQL_part2 = "";
		
		$valueArray = array();
		
		// Identify all of the data posted, using the DataTable scheme as a filter
		foreach($this->schema as $value)
		{
			$column = $value['column_name'];
			
			if(isset($data->$column))
			{
				$valueSQL_part1 .= ($valueSQL_part1 != "" ? ", " : "") . "`" . $column . "`";
				$valueSQL_part2 .= ($valueSQL_part2 != "" ? ", " : "") . "?";
				
				array_push($valueArray, $data->$column);
			}
		}
		
		// Insert the Data into the Database
		if($valueArray != array())
		{
			Database::query("INSERT INTO `" . $this->table . "` (" . $valueSQL_part1 . ") VALUES (" . $valueSQL_part2 . ")", $valueArray);
		}
	}
}
