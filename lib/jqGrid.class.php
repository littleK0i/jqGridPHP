<?php

abstract class jqGrid
{
	protected $grid_id;
	protected $input;

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
			'search_op' => null,
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

	protected $reserved_col_names = array('page', 'sidx', 'sord', 'nd', 'parent', 'nlevel', 'expanded', 'isLeaf');
	protected $internals = array('name', 'db', 'db_agg', 'unset', 'manual', 'search_op');

	protected $hacks = array(
		'implode_col_value'	=> true,
	);
									
	public function __construct(jqGridLoader $loader)
	{
		//------------------
		// Globals
		//------------------

		$this->grid_id 	= get_class($this);
		
		$this->loader	= $loader;
		$this->input	= $this->getInput();
		$this->DB		= $loader->getDB();

		$this->json_mode = $this->input('_json_mode');

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

	abstract protected function init();

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

		$callback = array($this, jqGridUtils::getFunctionName('out', $this->out));

		if(!is_callable($callback))
		{
			throw new jqGridException("Output type '{$this->out}' is not defined");
		}

		call_user_func($callback);
	}

	public function oper($oper)
	{
		#Common tasks for basic opers
		if(in_array($oper, array('add', 'edit', 'del')))
		{
			$id = $this->input($this->primary_key);
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
					$response = $this->opAdd($data);
				}
				elseif($oper == 'edit')
				{
					$response = $this->opEdit($id, $data);
				}

				$id = isset($response['new_id']) ? $response['new_id'] : $id;

				$this->operAfterAddEdit($id);
			break;

			case 'del':
				$response = $this->opDel($id);
			break;

			default:
				$callback = array($this, jqGridUtils::getFunctionName('op', $oper));

				if(!is_callable($callback))
				{
					throw new jqGridException("Oper $oper is not defined");
				}

				$response = call_user_func($callback);
			break;
		}

		$response = is_array($response) ? $response : array();

		if($ret = $this->operComplete($oper, $response))
		{
			$response = $ret;
		}

		//----------------
		// Output result
		//----------------

		$this->json($response);
	}
	
	/**
	 * Render grid
	 * 
	 * $jq_loader->render('jq_example');
	 *
	 * @param string $suffix [optional]
	 * @return string
	 */
	public function render($extend=null, $suffix=null)
	{
		$data = array();
		$data['extend'] = $extend;
		$data['suffix'] = $suffix ? jqGridUtils::checkAlphanum($suffix) : '';
		
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

			$colNames[] = $c['label'];
			$colModel[] = $this->renderColumn($k, $c);
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

	public function catchException(Exception $e)
	{
		$r = array(
			'error'	    => 1,
			'error_msg' => $e->getMessage(),
			'error_code'=> $e->getCode(),
			'error_data' => ($e instanceof jqGridException) ? $e->getData() : null,
		);

		if($this->loader->get('debug_output'))
		{
			$r['error_string'] = (string)$e;
		}

		//header('HTTP/1.1 500 Internal Server Error');
		$this->json($r);
	}

	protected function addRow(array $row)
	{
		#User modification of result row
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
			$cell[] = $this->addRowCell($k, $c, $row);
		}

		$cell = $this->addRowComplete($id, $cell, $row);

		$this->rows[] = array('id'   => $id, 'cell' => $cell);
	}

	protected function addRowCell($k, array $c, array $row)
	{
		$val = isset($row[$k]) ? $row[$k] : null;
		
		#Handle nulls
		if($val === null and $c['null'] !== null) $val = $c['null'];

		#Easy replace values
		if($c['replace']) $v = isset($c['replace'][$val]) ? $c['replace'][$val] : $val;

		return $val;
	}

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

	protected function afterInit()
	{
		//empty
	}

	protected function beforeInit()
	{
		//empty
	}

	//-----------------
	// Query builder
	//-----------------

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

	protected function buildFields($cols)
	{
		$fields = array();

		foreach($cols as $k => &$c)
		{
			if($c['manual']) continue;

			$fields[] = ($k == $c['db']) ? $c['db'] : ($c['db'] . ' AS ' . $k);
		}

		return implode(', ', $fields);
	}

	protected function buildFieldsAgg($cols)
	{
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

	protected function buildOrderBy($sidx, $sord)
	{
		if($sidx and $sord)
		{
			 return "ORDER BY $sidx $sord";
		}
	}

	protected function buildWhere(array $where, $glue, $type)
	{
		return $where ? implode($glue, $where) : 'true';
	}

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

	protected function getOutputParams()
	{
		$this->page  = max(1, intval($this->input('page', 1)));
		$this->limit = max(-1, intval($this->input['rows']));
		$this->sidx  = $this->input('sidx') ? jqGridUtils::checkAlphanum($this->input('sidx')) : '';
		$this->sord  = in_array($this->input('sord'), array('asc', 'desc')) ? $this->input('sord') : 'asc';

		$this->out	     = $this->input('_out', 'json');
	}

	protected function getDataAgg()
	{
		$query = $this->buildQueryAgg($this->query_agg ? $this->query_agg : $this->query);
		$result = $this->DB->query($query);
		
		$this->agg = $this->DB->fetch($result);
	}

	protected function getDataRows()
	{
		$query = $this->buildQueryRows($this->query);
		$result = $this->DB->query($query);

		while($r = $this->DB->fetch($result))
		{
			$this->addRow($r);
		}
	}

	protected function getDataUser(array $data)
	{
		#Agg
		if($this->agg)
		{
			$data['agg'] = $this->agg;
		}

		return $data;
	}

	protected function getInput()
	{
		$req = $_REQUEST; //dont modify the original request! ever!

		#Ajax input is always utf-8
		if($this->loader->get('encoding') != 'utf-8')
		{
			$iconv = function(&$val, $key, $enc_from, $enc_to)
			{
				$val = iconv($enc_from, $enc_to, $val);
			};

			array_walk_recursive($req, $iconv, 'utf-8', $this->loader->get('encoding'));
		}

		return $req;
	}

	protected function initColumn($k, array $c)
	{
		#Check reserved keys
		if(in_array($k, $this->reserved_col_names))
		{
			throw new jqGridException("Column name '$k' reserved for internal usage!");
		}

		#Check for reserved names
		if(strpos($k, '_') === 0)
		{
			throw new jqGridException("Column name must not begin with underscore!");
		}

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

		#Add special class to recognize cell align via CSS
		/*
		$classes = 'cell-align-'.$c['align'];
		$c['classes'] .= ($c['classes'] ? ' ' : '') . $classes;
		 */

		#Recognize search_op
		if(!$c['search_op'])
		{
			switch($c['formatter'])
			{
				case 'integer':
				case 'numeric':
				case 'currency':
					$c['search_op'] = 'numeric';
					break;

				default:
					$c['search_op'] = 'like';
					break;
			}
		}

		return $c;
	}

	//----------------
	// OPERATIONS PART
	//----------------

	protected function opAdd($ins)
	{
		return array('new_id' => $this->DB->insert($this->table, $ins, true));
	}

	protected function opEdit($id, $upd)
	{
		$result = $this->DB->update($this->table, $upd, array($this->primary_key => $id));
		return array('row_count' => $this->DB->rowCount($result));
	}

	protected function opDel($id)
	{
		#Delete single value
		if(is_numeric($id))
		{
			$result = $this->DB->delete($this->table, $id);
		}
		#Delete multiple value
		else
		{
			$ids = array_map('intval', explode(',', $id));
			$result = $this->DB->delete($this->table, $this->primary_key . ' IN (' . implode(',', $ids) . ')');
		}

		return array('row_count' => $this->DB->rowCount($result));
	}

	//----------------
	// OPER HOOKS PART
	//----------------

	protected function operData($r)
	{
		return $r;
	}

	protected function operAfterAddEdit($id)
	{
		return;
	}

	protected function operComplete($oper, $response)
	{
		if(!isset($response['success']))
		{
			$response['success'] = 1;
		}

		return $response;
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

	protected function outXml()
	{
		//unsupported
	}

	protected function outExport()
	{
		$type = $this->input('export');

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

	protected function parseRow($r)
	{
		return $r;
	}

	//----------------
	// RENDER PART
	//----------------

	protected function renderColumn($k, array $c)
	{
		#Remove internal column properties
		$c = array_diff_key($c, array_flip($this->internals));

		#Set the same name & index based on column key!
		$c['name']  = $k;
		$c['index'] = $k;

		#Hacks part
		if($this->hacks['implode_col_value'])
		{
			if( isset($c['edittype']) and $c['edittype'] == 'select'
			and isset($c['editoptions']['value']) and is_array($c['editoptions']['value']) )
			{
				$c['editoptions']['value'] = jqGridUtils::implodeColValue($c['editoptions']['value']);
			}

			if( isset($c['stype']) and $c['stype'] == 'select'
			and isset($c['searchoptions']['value']) and is_array($c['searchoptions']['value']) )
			{
				$c['searchoptions']['value'] = jqGridUtils::implodeColValue($c['searchoptions']['value']);
			}
		}

		return $c;
	}

	/**
	 * Base url to pass into 'url', 'editurl', 'cellurl'
	 * @return string
	 */
	protected function renderGridUrl()
	{
		return '?' . http_build_query(array($this->loader->get('input_grid') => $this->grid_id));
	}

	protected function renderNav($nav)
	{
		return $nav;
	}

	protected function renderOptions($opts)
	{
		return $opts;
	}

	/**
	 * Set grid's 'postData'
	 * @return array
	 */
	protected function renderPostData()
	{
		return array();
	}

	protected function renderComplete(array $data)
	{
		$code = '
			</script>

			<!-- Grid HTML -->
			<table id="'.$data['id'].$data['suffix'].'"></table>
			<div id="'.$data['pager_id'].$data['suffix'].'"></div>
 
			<!-- Grid JS -->
			<script>
			var pager = "#'.$data['pager_id'].$data['suffix'].'";

			var $grid = $("#'.$data['id'].$data['suffix'].'");
			var $'.$data['id'].$data['suffix'].' = $grid;

			$grid.jqGrid(';

		if(isset($data['extend']) and $data['extend'])
		{
			$code .= '$.extend('.jqGridUtils::jsonEncode($data['options']).', ' . $data['extend'] . ')';
		}
		else
		{
			$code .= jqGridUtils::jsonEncode($data['options']);
		}

		$code .= ");\n";

		#NavGrid
		if(isset($data['nav']))
		{
			$nav_special = array('prmEdit', 'prmAdd', 'prmDel', 'prmSearch', 'prmView');
			$code .= '$grid.jqGrid("navGrid", pager, ' . jqGridUtils::jsonEncode(array_diff_key($data['nav'], array_flip($nav_special)));

			#Respect the argument order
			foreach($nav_special as $k)
			{
				if(isset($data['nav'][$k]))
				{
					$code .= ', ' . jqGridUtils::jsonEncode($data['nav'][$k]);
				}
				else
				{
					$code .= ', null';
				}
			}

			$code .= ");\n";
		}

		return $code;
	}

	//----------------
	// SEARCH OPERATORS PART
	//----------------

	/**
	 * Build 'where' pieces based on the input
	 *
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

			$callback = array($this, jqGridUtils::getFunctionName('searchOp',$c['search_op']));

			if(!is_callable($callback))
			{
				throw new jqGridException('Search operation ' . $c['search_op'] . ' is not defined');
			}

			$wh = call_user_func($callback, $c, $val);
			if($wh) $this->where[] = $wh;
		}
	}

	protected function searchOpIgnore($c, $val)
	{
		return false;
	}

	protected function searchOpEqual($c, $val)
	{
		return $c['db'] . "	= '$val'";
	}

	protected function searchOpLike($c, $val)
	{
		#Escape wildcards
		$val = addcslashes($val, '%_');
		$val = addcslashes($val, '\\');
		
		return $c['db'] . " LIKE '%$val%'";
	}

	protected function searchOpNumeric($c, $val)
	{
		static $prefix = array( '<=', '>=', '<>', '!=', '<', '>', '=');

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

	protected function searchOpIn($c, $val)
	{
		$hash = array_map('trim', explode(',', $val));

		return $c['db'] . " IN ('" . implode("','", $hash) . "')";
	}

	protected function setColsDefault(array $vals)
	{
		$this->cols_default = array_merge($this->cols_default, $vals);
	}

	protected function setExportData(jqGrid_Export $lib)
	{
		$lib->grid_id  = $this->grid_id;
		$lib->input    = $this->input;

		$lib->cols     = $this->cols;
		$lib->rows     = $this->rows;
		$lib->userdata = $this->userdata;

		$lib->page     = $this->page;
		$lib->total    = $this->total;
		$lib->records  = $this->count;
	}

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
	 * @param string|array $key
	 * @param mixed $default
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
	 *
	 * @param  $obj - object to send
	 * @param null $mode - special modes for json output
	 * @return void
	 */
	protected function json($obj)
	{
		switch($this->json_mode)
		{
			case 'ajaxForm':
				header("Content-type: text/html; charset={$this->loader->get('encoding')};");
				//echo '<textarea>' . jqGridUtils::jsonEncode($obj) . '</textarea>';
				echo jqGridUtils::jsonEncode($obj);
			break;

			default:
				header("Content-type: application/json; charset={$this->loader->get('encoding')};");
				echo jqGridUtils::jsonEncode($obj);
			break;
		}
	}
}