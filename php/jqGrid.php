<?php

abstract class jqGrid
{
	protected $grid_id;
	protected $input;
	protected $DB;

	protected $loader;
	protected $table;
	protected $db_driver;
	protected $json_mode;
	
	protected $page;
	protected $limit;
	protected $sidx;
	protected $sord;
	protected $out = 'json';
	
	protected $offset;
	protected $count;
	protected $total;
	protected $userdata = array();
	
	protected $where  = array();
	protected $where_glue = ' AND ';

	protected $query;
	protected $query_agg;
	
	protected $primary_key;
	
	protected $do_agg = true;
	protected $do_search = true;
	protected $treegrid = false; //'adjacency'!, 'nested' is not supported

	protected $options = array();
	protected $nav   = array();
	
	protected $cols  = array();
	protected $rows  = array();
	protected $agg   = array();
	protected $debug = array();

	protected $response = array();

	#Local column default
	protected $cols_default = array();

	#Base defaults
	protected $default = array(

		'cols'	=> array(
			'label'     => '',
			'db' 	    => '',
			'db_agg'    => '',
			'unset'	    => false,
			'replace'   => null,
			'formatter' => null,
			'manual'    => false,
			'hidden'    => false,
			'editable'  => false,
			'search'    => true,
			'search_op' => 'auto',
			'classes'   => '',
			'align'	    => 'left',
			'null'		=> null,
		),

		'nav'	=> array(
			'add'	=> false,
			'edit'	=> false,
			'del'	=> false,
			'refresh' => true,
			'search' => false,
			'view'	=> false,
		),

		'options' => array(),
	);

	protected $reserved_col_names = array('page', 'sidx', 'sord', 'nd', 'parent', 'nlevel', 'expanded', 'isLeaf', 'oper');
	protected $internals = array('db', 'db_agg', 'unset', 'manual', 'search_op');

	/**
	 * Class constructor, initializes basic properties
	 *
	 * @param jqGridLoader $loader
	 */
	public function __construct(jqGridLoader $loader)
	{
		//------------------
		// Globals
		//------------------

		$this->grid_id 	= get_class($this);
		
		$this->loader	= $loader;
		$this->input	= $this->getInput();
		$this->DB		= $loader->loadDB();

		//----------------
		// Init
		//----------------

		$this->beforeInit();
		$this->init();
		$this->afterInit();

		//----------------
		// Prepare columns
		//----------------

		reset($this->cols);
		$this->primary_key = key($this->cols);

		foreach($this->cols as $k => &$c)
		{
			$c = $this->initColumn($k, $c);
		}
	}

	/**
	 * Abstract function to custom grid properties
	 * 
	 * @abstract
	 * @return void
	 */
	abstract protected function init();

	/**
	 * MAIN ACTION (1): Output data
	 *
	 * @throws jqGrid_Exception
	 * @return void
	 */
	public function output()
	{
		//----------------
		// Setup essential vars
		//----------------

		$this->getOutputParams();

		//----------------
		// Input to search
		//----------------

		if($this->do_search)
		{
			$this->search();
		}

		//----------------
		// Try to guess the basic query
		//----------------

		if(!$this->query)
		{
			$this->query = $this->getDefaultQuery();
		}

		//----------------
		// Get agg data
		//----------------

		if($this->do_agg)
		{
			$this->getDataAgg();
		}

		//----------------
		// Get rows data
		//----------------

		$this->getDataRows();
		
		//----------------
		// Build userdata
		//----------------

		$this->userdata = $this->getDataUser($this->userdata);

		//----------------
		// Set count automatically
		//----------------

		$this->setRowCount();

		//----------------
		// Do output
		//----------------

		$callback = array($this, jqGrid_Utils::getFunctionName('out', $this->out));

		if(!is_callable($callback))
		{
			throw new jqGrid_Exception("Output type '{$this->out}' is not defined");
		}

		call_user_func($callback);
	}

	/**
	 * MAIN ACTION (2): Perform operation to change data is any way
	 *
	 * @throws jqGrid_Exception
	 * @return void
	 */
	public function oper($oper)
	{
		#Common tasks for basic opers
		if(in_array($oper, array('add', 'edit', 'del')))
		{
			#Get ID
			$id = $this->input('id');
		}

		switch($oper)
		{
			case 'add':
			case 'edit':
				$data = array_intersect_key($this->input, $this->cols);
				unset($data[$this->primary_key]);

				$data = $this->operData($data);

				if($oper == 'add')
				{
					$id = $this->opAdd($data);
					$this->response['new_id'] = $id;
				}
				elseif($oper == 'edit')
				{
					$this->opEdit($id, $data);
				}

				$this->operAfterAddEdit($id);
			break;

			case 'del':
				$this->opDel($id);
			break;

			default:
				$callback = array($this, jqGrid_Utils::getFunctionName('op', $oper));

				if(is_callable($callback))
				{
					call_user_func($callback);
				}
				else
				{
					throw new jqGrid_Exception("Oper $oper is not defined");
				}
			break;
		}

		$this->response = array_merge(array('success' => 1), $this->response);

		$this->operComplete($oper);

		//----------------
		// Output result
		//----------------

		$this->json($this->response);
	}

	/**
	 * MAIN ACTION (3): Render grid
	 * 
	 * $jq_loader->render('jq_example');
	 *
	 * @param string $extend name of javascript variable to extend PHP-rendered options
	 * @param string $suffix suffix for grid_id. Use it if you need to set multiple grids on the same page
	 * @return string final javascript
	 */
	public function render($extend=null, $suffix=null)
	{
		$data = array();
		$data['extend'] = $extend;
		$data['suffix'] = $suffix ? jqGrid_Utils::checkAlphanum($suffix) : '';
		
		//------------------
		// Render ids
		//------------------
		
		$data['id'] = $this->grid_id;
		$data['pager_id'] = $this->grid_id . '_p';
		
		//-----------------
		// Render colModel
		//-----------------

		foreach($this->cols as $k => $c)
		{
			if(isset($c['unset']) and $c['unset']) continue;

			#Remove internal column properties
			$c = array_diff_key($c, array_flip($this->internals));
			
			$colModel[] = $this->renderColumn($c);
		}

		//-----------------
		// Render options
		//-----------------

		$opts = array(
			'colModel' => $colModel,
			'pager'	=> '#'.$data['pager_id'],
		);

		#URL's
		$opts['url'] = $opts['editurl'] = $opts['cellurl'] = $this->renderGridUrl();
		
		#Any postData?
		if($post_data = $this->renderPostData())
		{
			$opts['postData'] = $post_data;
		}
		
		$data['options'] = $this->renderOptions(array_merge($this->default['options'], $opts, $this->options));

		//-----------------
		// Render navigator
		//-----------------

		if(is_array($this->nav))
		{
			$data['nav'] = $this->renderNav(array_merge($this->default['nav'], $this->nav));
		}

		//-----------------
		// Compile the final string
		//-----------------

		return $this->renderComplete($data);
	}

	/**
	 * All exceptions comes here
	 * Override this method for custom error handling
	 *
	 * @param Exception $e
	 * @return void
	 */
	public function catchException(Exception $e)
	{
		$r = array(
			'error'	    => 1,
			'error_msg' => $e->getMessage(),
			'error_code'=> $e->getCode(),
			'error_data' => ($e instanceof jqGrid_Exception) ? $e->getData() : null,
			'error_type' => ($e instanceof jqGrid_Exception) ? $e->getType() : null,
		);

		if($this->loader->get('debug_output'))
		{
			$r['error_string'] = (string)$e;
		}

		//header('HTTP/1.1 500 Internal Server Error');
		$this->json($r);
	}

	/**
	 * (Output) Add new row to result set
	 * And also do related stuff
	 *
	 * @param array $row raw data from database
	 * @return void
	 */
	protected function addRow(array $row)
	{
		#Allow modification of result row without annoying overloading of 'addRow'
		$row = $this->parseRow($row);

		$id = $row[$this->primary_key];
		$cell = array();

		//----------------
		// Fill cells
		//----------------

		foreach($this->cols as $k => $c)
		{
			#Discard this column in output?
			if($c['unset']) continue;

			#Parse each cell
			$cell[] = $this->addRowCell($c, $row);
		}

		#.. and the whole row
		$cell = $this->addRowComplete($id, $cell, $row);

		$this->rows[] = array('id'   => $id, 'cell' => $cell);
	}

	/**
	 * (Output) Populate cell
	 *
	 * @param array $c - column options
	 * @param array $row - data row
	 * @return mixed - final cell value
	 */
	protected function addRowCell(array $c, array $row)
	{
		$val = isset($row[$c['name']]) ? $row[$c['name']] : null;
		
		#Handle nulls
		if($val === null and $c['null'] !== null) $val = $c['null'];

		#Easy replace values
		if($c['replace']) $val = isset($c['replace'][$val]) ? $c['replace'][$val] : $val;

		return $val;
	}

	/**
	 * (Output) Per-row result modification
	 *
	 * @param integer $id row_id
	 * @param array $cell - parsed cells
	 * @param array $row - data row
	 * @return array - modified $cell
	 */
	protected function addRowComplete($id, array $cell, array $row)
	{
		//----------------
		// TreeGrid handling
		//----------------

		if($this->treegrid == 'adjacency')
		{
			$cell[] = $row['level'];
			$cell[] = $row['parent'];
			$cell[] = $row['isLeaf'];
			$cell[] = $row['expanded'];
		}

		//----------------
		// Send all the vars beginning with '_' to userdata!
		//----------------

		foreach($row as $k => $v)
		{
			if(strpos($k, '_') === 0)
			{
				$this->userdata[$k][$id] = $v;
			}
		}

		return $cell;
	}

	/**
	 * Custom actions after 'init', but before columns init
	 * You may also overload __construct to add something in the very end
	 */
	protected function afterInit()
	{
		//empty
	}

	/**
	 * Custom actions before 'init'
	 * Place your signletons init here
	 */
	protected function beforeInit()
	{
		//empty
	}

	//-----------------
	// Query builder
	//-----------------

	/**
	 * (Output) Build complete AGG-query
	 *
	 * @param  string $q base query
	 * @return string complete query
	 */
	protected function buildQueryAgg($q)
	{
		//-----------------
		// Placeholders
		//-----------------

		$replace = array(
			'{where}'	=> $this->buildWhere($this->where, $this->where_glue, 'agg'),
			'{fields}'	=> $this->buildFieldsAgg($this->cols),
		);

		$q = strtr($q, $replace);

		$this->debug['query_agg'] = $q;

		return $q;
	}

	/**
	 * (Output) Build complete ROWS-query
	 *
	 * @param  string $q base query
	 * @return string complete query
	 */
	protected function buildQueryRows($q)
	{
		//-----------------
		// Placeholders
		//-----------------

		$replace = array(
			'{where}'	=> $this->buildWhere($this->where, $this->where_glue, 'rows'),
			'{fields}'	=> $this->buildFields($this->cols),
		);

		$q = strtr($q, $replace);

		//-----------------
		// ORDER BY, LIMIT, OFFSET
		//-----------------

		$q .= $this->buildOrderBy($this->sidx, $this->sord) . "\n";
		$q .= $this->buildLimitOffset($this->limit, $this->page) . "\n";

		$this->debug['query_rows'] = $q;

		return $q;
	}

	/**
	 * (Output) Implode {fields} for ROWS-query using 'db' property
	 *
	 * @param  array $cols grid columns
	 * @return string imploded list
	 */
	protected function buildFields(array $cols)
	{
		$fields = array();

		foreach($cols as $k => &$c)
		{
			if($c['manual']) continue;

			$fields[] = ($k == $c['db']) ? $c['db'] : ($c['db'] . ' AS ' . $k);
		}

		return implode(', ', $fields);
	}

	/**
	 * (Output) Implode {fields} for AGG-query useing 'db_agg' property
	 *
	 * @param  array $cols grid columns
	 * @return string imploded list
	 */
	protected function buildFieldsAgg($cols)
	{
		#Count should always be here!
		$fields = array('count(*) AS _count');

		foreach($cols as $k => $c)
		{
			if(!$c['db_agg']) continue;

			switch($c['db_agg'])
			{
				#Common
				case 'sum':
				case 'avg':
				case 'count':
				case 'min':
				case 'max':
					$fields[] = $c['db_agg'] . '(' . $c['db'] . ') AS ' . $k;
					break;

				#Custom
				default:
					$fields[] = $c['db_agg'] . ' AS ' . $k;
					break;
			}
		}

		return implode(', ', $fields);
	}

	/**
	 * (Output) Builds paging for ROWS-query
	 * LIMIT x OFFSET y
	 * 
	 * Alter this if your database requires other syntax
	 *
	 * @param  integer $limit
	 * @param  integer $page
	 * @return string
	 */
	protected function buildLimitOffset($limit, $page)
	{
		$limit = intval($limit);
		$page = intval($page);

		if($limit > 0 and $page > 0)
		{
			$offset = ($page * $limit) - $limit;
			return "LIMIT $limit OFFSET $offset";
		}

		return '';
	}

	/**
	 * (Output) Builds sorting for ROWS-query
	 * Overload it to introduce more complex
	 *
	 * Input is checked in 'getOutputParamas', so we trust it
	 *
	 * @param  string $sidx field
	 * @param  string $sord order
	 * @return string
	 */
	protected function buildOrderBy($sidx, $sord)
	{
		if($sidx and $sord)
		{
			 return "ORDER BY $sidx $sord";
		}
	}

	/**
	 * (Output) Builds {where} both for ROWS and AGG queries
	 * Simple imploding for now
	 *
	 * @param  array $where - filtering SQL-expressions
	 * @param  string $glue - ' AND ' or ' OR '
	 * @return string - 'agg' or 'rows' query type, if you need the different conditions
	 */
	protected function buildWhere(array $where, $glue, $type)
	{
		return $where ? implode($glue, $where) : 'true';
	}

	/**
	 * (Output) Generate default query if only $this->table was set
	 * 
	 * @return string
	 */
	protected function getDefaultQuery()
	{
		if(!$this->table)
		{
			return '';
		}

		return "
			SELECT {fields}
			FROM {$this->table}
			WHERE {where}
		";
	}

	/**
	 * (Output) Get and validate params needed for output
	 */
	protected function getOutputParams()
	{
		$this->page  = max(1, intval($this->input('page', 1)));
		$this->limit = max(-1, intval($this->input['rows']));
		$this->sidx  = $this->input('sidx') ? jqGrid_Utils::checkAlphanum($this->input('sidx')) : '';
		$this->sord  = in_array($this->input('sord'), array('asc', 'desc')) ? $this->input('sord') : 'asc';

		$this->out   = $this->input('_out', 'json');
	}

	/**
	 * (Output) Get AGG data - over the whole result set
	 * Saves it to $this->agg
	 */
	protected function getDataAgg()
	{
		$query = $this->buildQueryAgg($this->query_agg ? $this->query_agg : $this->query);
		$result = $this->DB->query($query);
		
		$this->agg = $this->DB->fetch($result);
	}

	/**
	 * (Output) Get ROWS data - the current page
	 * Saves it to $this->rows via 'addRow'
	 */
	protected function getDataRows()
	{
		$query = $this->buildQueryRows($this->query);
		$result = $this->DB->query($query);

		while($r = $this->DB->fetch($result))
		{
			$this->addRow($r);
		}
	}

	/**
	 * (Output) Build 'userdata'
	 *
	 * @param array $userdata orignal value
	 * @return array final value
	 */
	protected function getDataUser(array $userdata)
	{
		#Agg
		if($this->agg)
		{
			$userdata['agg'] = $this->agg;
		}

		return $userdata;
	}


	/**
	 * This is the only entry-point for external data
	 *
	 * @return array final input
	 */
	protected function getInput()
	{
		$req = $_REQUEST; //do not modify the original request! ever!

		#Ajax input is always utf-8 -> convert it
		if($this->loader->get('encoding') != 'utf-8' and isset($_SERVER['X-Requested-With']))
		{
			array_walk_recursive($req, array('jqGrid_Utils', 'iconvWalk'), 'utf-8', $this->loader->get('encoding'));
		}

		return $req;
	}

	/**
	 * Inits one column
	 * Apply defaults etc.
	 *
	 * @throws jqGrid_Exception
	 * @param string $k - column name
	 * @param array $c - user-inputed non-default column params
	 * @return array - complete column
	 */
	protected function initColumn($k, array $c)
	{
		#Check reserved keys
		if(in_array($k, $this->reserved_col_names))
		{
			throw new jqGrid_Exception("Column name '$k' reserved for internal usage!");
		}

		#Check for reserved names
		if(strpos($k, '_') === 0)
		{
			throw new jqGrid_Exception("Column name must NOT begin with underscore!");
		}

		#Name and index always matches the array key
		#For your own sake!!
		$c['name']  = $k;
		$c['index'] = $k;

		#Name = column key if not set
		if(!isset($c['label'])) $c['label'] = $k;

		#Merge with defaults
		$c = array_merge($this->default['cols'], $this->cols_default, $c);

		#DB = column key if not set
		$c['db'] = $c['db'] ? $c['db'] : $k;

		#User specified primary_key?
		if(isset($c['key']) and $c['key'])
		{
			$this->primary_key = $k;
		}

		return $c;
	}

	//----------------
	// OPERATIONS PART
	//----------------

	/**
	 * (Oper) Insert
	 *
	 * Please note: this is the only "Oper" function, that must return new row id
	 *
	 * @param  array $ins form data
	 * @return integer|string new_id
	 */
	protected function opAdd(array $ins)
	{
		return $this->DB->insert($this->table, $ins, true);
	}

	/**
	 * (Oper) Update
	 *
	 * @param  integer $id - row_id to update
	 * @param  array $upd form data
	 * @return void
	 */
	protected function opEdit($id, array $upd)
	{
		$this->DB->update($this->table, $upd, array($this->primary_key => $id));
	}

	/**
	 * (Oper) Delete
	 *
	 * @param  integer|string $id - one or multiple id's to delete
	 * @return void
	 */
	protected function opDel($id)
	{
		#Delete single value
		if(is_numeric($id))
		{
			$this->DB->delete($this->table, array($this->primary_key => $id));
		}
		#Delete multiple value
		else
		{
			$ids = array_map('intval', explode(',', $id));
			$this->DB->delete($this->table, $this->primary_key . ' IN (' . implode(',', $ids) . ')');
		}
	}

	//----------------
	// OPER HOOKS PART
	//----------------

	/**
	 * (Oper) Modify form data for opAdd and opEdit only
	 * These operations usually need the same modifications, so i made a stand-alone hook for them
	 * 
	 * @param  array $r form data
	 * @return array modified form data
	 */
	protected function operData(array $r)
	{
		return $r;
	}

	/**
	 * (Oper) Hook after opAdd and opEdit
	 * Useful for uploading images after processing other data etc.
	 *
	 * @param $id id of updated or inserted row
	 * @return void
	 */
	protected function operAfterAddEdit($id)
	{
		return;
	}

	/**
	 * (Oper) Hook after ALL oper's
	 * Useful for cleanup and dropping caches
	 *
	 * @param  $oper
	 * @return void
	 */
	protected function operComplete($oper)
	{
		return;
	}

	//----------------
	// OUTPUT PART
	//----------------

	/**
	 * Output json
	 */
	protected function outJson()
	{
		$r->page    =& $this->page;
		$r->total   =& $this->total;
		$r->records =& $this->count;
		$r->rows 	=& $this->rows;

		$r->userdata = $this->userdata;

		if($this->loader->get('debug_output'))
		{
			$r->debug =& $this->debug;
		}

		$this->json($r);
	}

	/**
	 * Output XML ... not supported
	 */
	protected function outXml()
	{
		//unsupported
	}

	/**
	 * Export data using plugin
	 */
	protected function outExport()
	{
		$type = jqGrid_Utils::checkAlphanum($this->input('export'));

		$class = 'jqGrid_Export_' . ucfirst($type);

		if(!class_exists($class))
		{
			throw new jqGrid_Exception("Export type $type does not exist");
		}

		#Weird >__<
		$lib = new $class($this->loader, $this, $this->input);
		$this->setExportData($lib);
		$lib->doExport();
	}

	/**
	 * (Output) Modify row data before output
	 *
	 * @param  array $r row data
	 * @return array modified row data
	 */
	protected function parseRow(array $r)
	{
		return $r;
	}

	//----------------
	// RENDER PART
	//----------------

	/**
	 * (Render) Render single column
	 *
	 * @param array $c col properties
	 * @return array col properties to be rendered
	 */
	protected function renderColumn(array $c)
	{
		return $c;
	}

	/**
	 * (Render) Base url to pass into 'url', 'editurl', 'cellurl'
	 *
	 * @return string
	 */
	protected function renderGridUrl()
	{
		return '?' . http_build_query(array($this->loader->get('input_grid') => $this->grid_id));
	}

	/**
	 * (Render) Alter 'nav'
	 *
	 * @param  $nav original nav
	 * @return array modified nav
	 */
	protected function renderNav(array $nav)
	{
		return $nav;
	}

	/**
	 * (Render) Alter 'options'
	 *
	 * @param  array $opts original options
	 * @return array modified options
	 */
	protected function renderOptions(array $opts)
	{
		return $opts;
	}

	/**
	 * (Render) Special hook for setting option 'postData'
	 * Oftenly used to set explicit invisible filters
	 *
	 * @return array postData
	 */
	protected function renderPostData()
	{
		return array();
	}

	/**
	 * (Render) Takes all previously generated parts and combine them into general output string
	 * You can completely override the rendering strategy here
	 *
	 * @param array $data
	 * @return string
	 */
	protected function renderComplete(array $data)
	{
		$code = '
document.write(\'<table id="'.$data['id'].$data['suffix'].'"></table>\');
document.write(\'<div id="'.$data['pager_id'].$data['suffix'].'"></div>\');

var pager = "#'.$data['pager_id'].$data['suffix'].'";

var $grid = $("#'.$data['id'].$data['suffix'].'");
var $'.$data['id'].$data['suffix'].' = $grid;

$grid.jqGrid(';

		if(isset($data['extend']) and $data['extend'])
		{
			$code .= '$.extend('.jqGrid_Utils::jsonEncode($data['options']).', ' . $data['extend'] . ')';
		}
		else
		{
			$code .= jqGrid_Utils::jsonEncode($data['options']);
		}

		$code .= ");\n";

		#NavGrid
		if(isset($data['nav']))
		{
			$nav_special = array('prmEdit', 'prmAdd', 'prmDel', 'prmSearch', 'prmView');
			$code .= '$grid.jqGrid("navGrid", pager, ' . jqGrid_Utils::jsonEncode(array_diff_key($data['nav'], array_flip($nav_special)));

			#Respect the argument order
			foreach($nav_special as $k)
			{
				if(isset($data['nav'][$k]))
				{
					$code .= ', ' . jqGrid_Utils::jsonEncode($data['nav'][$k]);
				}
				else
				{
					$code .= ', null';
				}
			}

			$code .= ");\n";

			#Excel button
			if(isset($data['nav']['excel']) and $data['nav']['excel'])
			{
				$code .= '$grid.jqGrid("navButtonAdd", pager, {caption: "'.$data['nav']['exceltext'].'", title: "'.$data['nav']['exceltext'].'", icon: "ui-extlink", onClickButton: function(){ $(this).jqGrid("extExport", {"export" : "ExcelHtml", "rows": -1}); }});' . "\n";
			}
		}

		return $code;
	}

	//----------------
	// SEARCH OPERATORS PART
	//----------------


	/**
	 * (Output) Perform searching based on input
	 * Populates $this->where with SQL-expressions
	 * 
	 * @throws jqGrid_Exception
	 * @return void
	 */
	protected function search()
	{
		foreach($this->cols as $k => &$c)
		{
			if(!isset($this->input[$k]) or $this->input[$k] === '' or $c['manual'])
			{
				continue;
			}

			if(is_array($this->input[$k])) continue;

			$val = trim($this->DB->quote($this->input[$k]), "'");

			//------------------
			// Apply search operator
			//------------------

			$callback = array($this, jqGrid_Utils::getFunctionName('searchOp',$c['search_op']));

			if(!is_callable($callback))
			{
				throw new jqGrid_Exception('Search operation ' . $c['search_op'] . ' is not defined');
			}

			$wh = call_user_func($callback, $c, $val);
			if($wh) $this->where[] = $wh;
		}
	}

	/**
	 * Auto detect of searchOp
	 *
	 * @param  $c column settings
	 * @param  $val "clean" value. If you need original value - use $this->input
	 * @return string filering SQL-expression -> goes directly to WHERE. Set false to skip search.
	 */
	protected function searchOpAuto($c, $val)
	{
		#Search type - select?
		if(isset($c['stype']) and $c['stype'] == 'select')
		{
			return self::searchOpEqual($c, $val);
		}

		#Numeric by formatter?
		if(isset($c['formatter']) and in_array($c['formatter'], array('integer', 'numeric', 'currency')))
		{
			return self::searchOpNumeric($c, $val);	
		}

		#Numeric by value?
		if(preg_match('#^([<>=!]{1,2})?\d+$#', $val))
		{
			return self::searchOpNumeric($c, $val);
		}

		#Seems to be 'like'
		return self::searchOpLike($c, $val);
	}

	/**
	 * Disable search
	 */
	protected function searchOpIgnore($c, $val)
	{
		return false;
	}

	/**
	 * Look for EXACT match
	 */
	protected function searchOpEqual($c, $val)
	{
		return $c['db'] . "	= '$val'";
	}

	/**
	 * Look for similar text
	 */
	protected function searchOpLike($c, $val)
	{
		#Escape wildcards
		$val = addcslashes($val, '%_');
		$val = addcslashes($val, '\\');

		$op = ($this->DB->getType() == 'postgresql') ? 'ILIKE' : 'LIKE';
		
		return $c['db'] . " $op '%$val%'";
	}

	/**
	 * Look for list of values
	 */
	protected function searchOpIn($c, $val)
	{
		$hash = array_map('trim', explode(',', $val));

		return $c['db'] . " IN ('" . implode("','", $hash) . "')";
	}

	/**
	 * Look for numeric value
	 * Supports prefixes to search for range of numeric values
	 */
	protected function searchOpNumeric($c, $val)
	{
		static $prefix = array('<=', '>=', '<>', '!=', '<', '>', '=');

		foreach($prefix as $p)
		{
			if(strpos($val, $p) === 0)
			{
				$op = $p;
				$val = substr($val, strlen($p));
				break;
			}
		}

		$op	 = isset($op) ? $op : '=';
		$val = floatval(trim($val));

		return $c['db'] . " $op '$val'";
	}

	/**
	 * Send data to export library
	 * 
	 * @param jqGrid_Export $lib
	 * @return void
	 */
	protected function setExportData(jqGrid_Export $lib)
	{
		$lib->grid_id  = $this->grid_id;
		$lib->input    = $this->input;
		$lib->DB       = $this->DB;

		$lib->cols     = $this->cols;
		$lib->rows     = $this->rows;
		$lib->userdata = $this->userdata;

		$lib->page     = $this->page;
		$lib->total    = $this->total;
		$lib->records  = $this->count;
	}

	/**
	 * Set total row count and calc related vars
	 *
	 * @throws jqGrid_Exception
	 * @param integer $count - set argument to override auto-detection
	 * @return
	 */
	protected function setRowCount($count=null)
	{
		if(is_null($count))
		{
			#Retrieve count from agg data
			if(isset($this->agg['_count']))
			{
				$count = $this->agg['_count'];
			}
			#Simply count 'rows'
			else
			{
				$count = count($this->rows);
			}
		}

		$count = intval($count);

		if($count < 0)
		{
			throw new jqGrid_Exception('Invalid count value');
		}

		if(!$count)
		{
			$this->count 	= 0;
			$this->page 	= 0;
			$this->total 	= 0;
			$this->offset	= 0;

			return;
		}

		$this->count = $count;

		if($this->limit == -1)
		{
			$this->total  = 1;
			$this->page   = 1;
			$this->offset = 0;
		}
		elseif($this->limit)
		{
			$this->total = ($this->count > 0) ? ceil($this->count / $this->limit) : 0;

			$this->page  = ($this->page <= $this->total) ? $this->page : $this->total;
			$this->offset = $this->limit * $this->page - $this->limit;
		}
	}

	//----------------
	// HELPER PART
	//----------------

	/**
	 * Get input var(s) by key(s)
	 * Nice shortcut against repeating "isset" everywhere
	 * 
	 * @param string|array $key single key or set of keys
	 * @param mixed $default default value in case the key does not exist
	 * @return mixed
	 */
	protected function input($key, $default=null)
	{
		if(is_array($key))
		{
			$ret = array();

			foreach($key as $k)
			{
				$ret[$k] = $this->input($k, $default);	
			}

			return $ret;
		}
		
		return isset($this->input[$key]) ? $this->input[$key] : $default;
	}

	/**
	 * Send JSON to browser
	 * Please set $this->json_mode for special output
	 *
	 * TODO: add jsonp support
	 *
	 * @param  $obj object to send
	 * @return void
	 */
	protected function json($obj)
	{
		#Mode preset
		if($this->json_mode)
		{
			$mode = $this->json_mode;
		}
		#Common jQuery request
		elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			$mode = 'json';
		}
		#Probably ajaxForm iframe
		else
		{
			$mode = 'ajaxForm';
		}

		switch($mode)
		{
			case 'ajaxForm':
				header("Content-type: text/html; charset={$this->loader->get('encoding')};");
				//echo '<textarea>' . jqGrid_Utils::jsonEncode($obj) . '</textarea>';
				echo jqGrid_Utils::jsonEncode($obj);
			break;

			default:
				header("Content-type: application/json; charset={$this->loader->get('encoding')};");
				echo jqGrid_Utils::jsonEncode($obj);
			break;
		}
	}
}