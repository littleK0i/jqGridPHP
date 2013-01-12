<?php

abstract class jqGrid
{
    /** @var $DB jqGrid_DB */
    protected $DB;

    /** @var $Loader jqGridLoader */
    protected $Loader;

    protected $grid_id;
    protected $base_url;

    protected $input;

    protected $page;
    protected $limit;
    protected $sidx;
    protected $sord;
    protected $out = 'json';
    protected $json_mode;

    protected $offset;
    protected $count;
    protected $total;

    protected $where = array();
    protected $where_glue = ' AND ';
    protected $where_empty = 'true';

    protected $table;
    protected $query;
    protected $query_agg;

    protected $primary_key = array();
    protected $primary_key_glue = '_';
    protected $primary_key_auto_increment = null;

    protected $do_agg = true;
    protected $do_search = true;
    protected $do_search_advanced = true;
    protected $do_sort = true;
    protected $do_limit = true;

    protected $treegrid = false; //'adjacency' or 'nested'

    protected $options = array();
    protected $nav = array();

    protected $cols = array();
    protected $rows = array();
    protected $agg = array();
    protected $userdata = array();
    protected $debug = array();

    protected $response = array();

    #Local column default
    protected $cols_default = array();

    #Base defaults
    protected $default = array(

        'cols' => array(
            'label' => '',
            'db' => '',
            'db_agg' => '',
            'unset' => false,
            'replace' => null,
            'formatter' => null,
            'manual' => false,
            'hidden' => false,
            'editable' => false,
            'search' => true,
            'search_op' => 'auto',
            'classes' => '',
            'align' => 'left',
            'null' => null,
            'encode' => true,
        ),

        'nav' => array(
            'add' => false,
            'edit' => false,
            'del' => false,
            'refresh' => true,
            'search' => false,
            'view' => false,
        ),

        'options' => array(
            'datatype' => 'json',
            'mtype' => 'POST',
            'loadui' => 'block',
            'prmNames' => array(
                'id' => '_id',
            ),
        ),

        'reserved_col_names' => array('page', 'sidx', 'sord', 'nd', 'oper', 'filters', 'rd'),
    );

    protected $reserved_col_names;
    protected $internal_col_prop = array('db', 'db_agg', 'unset', 'manual', 'search_op');
    protected $query_placeholders = array('fields' => '{fields}', 'where' => '{where}');

    protected $render_data = array();
    protected $render_extend = 'opts';
    protected $render_suffix_col = null;
    protected $render_filter_toolbar = false;

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

        $this->grid_id = get_class($this);

        $this->Loader = $loader;
        $this->input = $this->getInput();
        $this->DB = $loader->loadDB();

        //----------------
        // Init
        //----------------

        $this->beforeInit();
        $this->init();
        $this->afterInit();

        //----------------
        // Primary key
        //----------------

        reset($this->cols);
        $this->primary_key = $this->primary_key ? (array)$this->primary_key : array(key($this->cols));

        if(is_null($this->primary_key_auto_increment))
        {
            $this->primary_key_auto_increment = count($this->primary_key) == 1;
        }

        //----------------
        // Prepare columns
        //----------------

        $this->reserved_col_names = $this->getReservedColNames();

        foreach($this->cols as $k => &$c)
        {
            $c = $this->initColumn($k, $c);
        }
    }

    /**
     * Abstract function for setting grid properties
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

        if($this->do_search_advanced)
        {
            $this->searchAdvanced();
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

        if(is_null($this->count))
        {
            $this->setRowCount();
        }

        //----------------
        // Do output
        //----------------

        $callback = array($this, jqGrid_Utils::uscore2camel('out', $this->out));

        if(!is_callable($callback))
        {
            throw new jqGrid_Exception("Output type '{$this->out}' is not defined");
        }

        call_user_func($callback);
    }

    /**
     * MAIN ACTION (2): Perform operation to change data is any way
     *
     * @param $oper - operation name
     * @return void
     */
    public function oper($oper)
    {
        $id = $this->input('_id');
        $oper = strval($oper);

        switch($oper)
        {
            case 'add':
                $data = array_intersect_key($this->input, $this->cols);
                $data = $this->operData($data);

                $id = $this->opAdd($data);

                #Not auto increment -> build new_id from data
                if(empty($this->primary_key_auto_increment))
                {
                    $id = $this->implodePrimaryKey($data);
                }

                $this->response['new_id'] = $id;

                $this->operAfterAddEdit($id);
                break;

            case 'edit':
                $data = array_intersect_key($this->input, $this->cols);
                $data = $this->operData($data);

                $this->opEdit($id, $data);

                $this->operAfterAddEdit($id);
                break;

            case 'del':
                $this->opDel($id);
                break;

            default:
                $callback = array($this, jqGrid_Utils::uscore2camel('op', $oper));

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
     * @param array $render_data
     * @return string
     */
    public function render($render_data = array())
    {
        if(!is_array($render_data))
        {
            throw new jqGrid_Exception_Render('Render data must be an array');
        }

        $this->render_data = $render_data;

        //------------------
        // Basic data
        //------------------

        $data = array();

        $data['extend'] = $this->render_extend;
        $data['suffix'] = $this->renderGridSuffix($render_data);

        //------------------
        // Render ids
        //------------------

        $data['id'] = $this->grid_id . $data['suffix'];
        $data['pager_id'] = $this->grid_id . $data['suffix'] . '_p';

        //-----------------
        // Render colModel
        //-----------------

        foreach($this->cols as $k => $c)
        {
            if(isset($c['unset']) and $c['unset']) continue;

            #Remove internal column properties
            $c = array_diff_key($c, array_flip($this->internal_col_prop));

            $colModel[] = $this->renderColumn($c);
        }

        //-----------------
        // Render options
        //-----------------

        $options = array(
            'colModel' => $colModel,
            'pager' => '#' . $data['pager_id'],
        );

        #URL's
        $options['url'] = $options['editurl'] = $options['cellurl'] = $this->renderGridUrl();

        #Any postData?
        if($post_data = $this->renderPostData())
        {
            $options['postData'] = $post_data;
        }

        $data['options'] = $this->renderOptions(array_merge($this->default['options'], $options, $this->options));

        //-----------------
        // Render navigator
        //-----------------

        if(is_array($this->nav))
        {
            $data['nav'] = $this->renderNav(array_merge($this->default['nav'], $this->nav));
        }

        //------------------
        // Render base html
        //------------------

        $data['html'] = $this->renderHtml($data);

        //-----------------
        // Compile the final string
        //-----------------

        return $this->renderComplete($data);
    }

    /**
     * All exceptions comes here
     * Override this method for custom exception handling
     *
     * @param jqGrid_Exception $e
     * @return mixed
     */
    public function catchException(jqGrid_Exception $e)
    {
        #More output types will be added
        switch($e->getOutputType())
        {
            case 'json':
                $r = array(
                    'error' => 1,
                    'error_msg' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_data' => $e->getData(),
                    'error_type' => $e->getExceptionType(),
                );

                if($this->Loader->get('debug_output'))
                {
                    $r['error_string'] = (string)$e;
                }
                else
                {
                    if($e instanceof jqGrid_Exception_DB)
                    {
                        unset($r['error_data']['query']);
                    }
                }

                $this->json($r);
                break;

            case 'trigger_error':
                trigger_error($e->getMessage(), E_USER_ERROR);
                break;
        }

        return $e;
    }

    /**
     * (Output) Add new row to result set
     * And also do related stuff
     *
     * @param array $row - raw data from database
     * @return void
     */
    protected function addRow($row)
    {
        #Allow modification of result row without annoying overloading of 'addRow'
        $row = $this->parseRow($row);

        $id = $this->implodePrimaryKey($row);
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

        #Prase whole row
        $cell = $this->addRowComplete($id, $cell, $row);

        $this->rows[] = array('id' => $id, 'cell' => $cell);
    }

    /**
     * (Output) Populate cell
     *
     * @param array $c - column options
     * @param array $row - data row
     * @return mixed - final cell value
     */
    protected function addRowCell($c, $row)
    {
        $val = isset($row[$c['name']]) ? $row[$c['name']] : null;

        #Handle nulls
        if($val === null and $c['null'] !== null) $val = $c['null'];

        #Easy replace values
        if($c['replace']) $val = isset($c['replace'][$val]) ? $c['replace'][$val] : $val;

        #Encode before output
        if($c['encode']) $val = $this->outputEncodeValue($c, $val);

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
    protected function addRowComplete($id, $cell, $row)
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
            $cell[] = isset($row['loaded']) ? $row['loaded'] : false;
        }

        if($this->treegrid == 'nested')
        {
            $cell[] = $row['level'];
            $cell[] = $row['lft'];
            $cell[] = $row['rgt'];
            $cell[] = $row['isLeaf'];
            $cell[] = $row['expanded'];
            $cell[] = isset($row['loaded']) ? $row['loaded'] : false;
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
     * Place your singletons init here
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
     * @param  string $q - base query
     * @return string - final query
     */
    protected function buildQueryAgg($q)
    {
        //-----------------
        // Placeholders
        //-----------------

        $replace = array(
            $this->query_placeholders['where'] => $this->buildWhere($this->where, $this->where_glue, 'agg'),
            $this->query_placeholders['fields'] => $this->buildFieldsAgg($this->cols),
        );

        $q = strtr($q, $replace);

        return $q;
    }

    /**
     * (Output) Build complete ROWS-query
     *
     * @param  string $q - base query
     * @return string - final query
     */
    protected function buildQueryRows($q)
    {
        //-----------------
        // Placeholders
        //-----------------

        $replace = array(
            $this->query_placeholders['where'] => $this->buildWhere($this->where, $this->where_glue, 'rows'),
            $this->query_placeholders['fields'] => $this->buildFields($this->cols),
        );

        $q = strtr($q, $replace);

        //-----------------
        // ORDER BY, LIMIT, OFFSET
        //-----------------

        if($this->do_sort) $q .= $this->buildOrderBy($this->sidx, $this->sord) . "\n";
        if($this->do_limit) $q .= $this->buildLimitOffset($this->limit, $this->page) . "\n";

        return $q;
    }

    /**
     * (Output) Implode {fields} for ROWS-query using 'db' property
     *
     * @param  array $cols - grid columns
     * @return string - imploded list
     */
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

    /**
     * (Output) Implode {fields} for AGG-query using 'db_agg' property
     *
     * @param  array $cols - grid columns
     * @return string - imploded list
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
     * Input is checked in 'getOutputParams', so we trust it
     *
     * @param  string $sidx - field name
     * @param  string $sord - order (asc, desc)
     * @return string
     */
    protected function buildOrderBy($sidx, $sord)
    {
        if($sidx and $sord)
        {
            return "ORDER BY $sidx $sord";
        }

        return '';
    }

    /**
     * (Output) Builds {where} both for ROWS and AGG queries
     *
     * @param array $where - array with conditions
     * @param string $glue - glue string (' AND ', ' OR ')
     * @param string $type - query type ('agg', 'rows')
     * @return string - imploded condition string
     */
    protected function buildWhere($where, $glue, $type)
    {
        return $where ? implode($glue, $where) : $this->where_empty;
    }

    /**
     * (Output) Generate default query if only $this->table was set
     *
     * @return string
     */
    protected function getDefaultQuery()
    {
        if(empty($this->table))
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
        $this->page = max(1, intval($this->input('page', 1)));
        $this->limit = max(-1, intval($this->input['rows']));
        $this->sidx = $this->input('sidx') ? jqGrid_Utils::checkAlphanum($this->input('sidx')) : $this->primary_key[0];
        $this->sord = in_array($this->input('sord'), array('asc', 'desc')) ? $this->input('sord') : 'asc';

        $this->out = $this->input('_out', 'json');
    }

    /**
     * (Output) Get AGG data - over the whole result set
     * Save it to $this->agg
     */
    protected function getDataAgg()
    {
        $query = $this->buildQueryAgg($this->query_agg ? $this->query_agg : $this->query);
        $result = $this->DB->query($query);

        $this->agg = $this->DB->fetch($result);

        $this->debug['query_agg'] = $query;
    }

    /**
     * (Output) Get ROWS data - the current page
     * Save it to $this->rows via 'addRow'
     */
    protected function getDataRows()
    {
        $query = $this->buildQueryRows($this->query);
        $result = $this->DB->query($query);

        while($r = $this->DB->fetch($result))
        {
            $this->addRow($r);
        }

        $this->debug['query_rows'] = $query;
    }

    /**
     * (Output) Build 'userdata'
     *
     * @param array $userdata - orignal value
     * @return array - final value
     */
    protected function getDataUser($userdata)
    {
        if($this->agg)
        {
            $userdata['agg'] = $this->agg;
        }

        return $userdata;
    }


    /**
     * It is the ONLY entry-point for external data
     *
     * @return array
     */
    protected function getInput()
    {
        $req = array_merge($_GET, $_POST);

        #Ajax input is always utf-8 -> convert it
        if($this->Loader->get('encoding') != 'utf-8' and isset($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            $req = jqGrid_Utils::arrayIconv($req, 'utf-8', $this->Loader->get('encoding'));
        }

        return $req;
    }

    /**
     * Get reserved column names specific to this grid
     *
     * @return array - array of names
     */
    protected function getReservedColNames()
    {
        $names = $this->default['reserved_col_names'];

        if($this->treegrid == 'adjacency')
        {
            $names = array_merge($names, array('parent', 'level', 'isLeaf', 'expanded', 'loaded'));
        }

        if($this->treegrid == 'nested')
        {
            $names = array_merge($names, array('level', 'lft', 'rgt', 'isLeaf', 'expanded', 'loaded'));
        }

        return $names;
    }

    /**
     * Get row from DB
     *
     * @param $primary_key
     * @return array
     */
    protected function getRowByPrimaryKey($primary_key)
    {
        $where = array();

        foreach($this->explodePrimaryKey($primary_key) as $k => $v)
        {
            $where[] = "$k = " . $this->DB->quote($v);
        }

        $result = $this->DB->query("SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where));
        return $this->DB->fetch($result);
    }

    /**
     * Init one column
     * Apply defaults, check name etc.
     *
     * @param string $k - column name
     * @param array $c - non-default column params
     * @return array - complete column
     */
    protected function initColumn($k, $c)
    {
        #Check reserved keys
        if(in_array($k, $this->reserved_col_names))
        {
            throw new jqGrid_Exception("Column name '$k' reserved for internal usage!");
        }

        if(strpos($k, '_') === 0)
        {
            throw new jqGrid_Exception("Column name '$k' must NOT begin with underscore!");
        }

        #Name and index always match the array key
        $c['name'] = $k;
        $c['index'] = $k;

        #Label = column key if not set
        if(!isset($c['label'])) $c['label'] = $k;

        #Merge with defaults
        $c = array_merge($this->default['cols'], $this->cols_default, $c);

        #DB = column key if not set
        $c['db'] = $c['db'] ? $c['db'] : $k;

        return $c;
    }

    //----------------
    // OPERATIONS PART
    //----------------

    /**
     * (Oper) Insert
     *
     * Please note: this is the only "Oper" function, which must return new row id
     *
     * @param  array $ins - form data
     * @return integer - new_id
     */
    protected function opAdd($ins)
    {
        if(empty($this->table))
        {
            throw new jqGrid_Exception('Table is not defined');
        }

        return $this->DB->insert($this->table, $ins, true);
    }

    /**
     * (Oper) Update
     *
     * @param  integer $id - id to update
     * @param  array $upd - form data
     * @return void
     */
    protected function opEdit($id, $upd)
    {
        if(empty($this->table))
        {
            throw new jqGrid_Exception('Table is not defined');
        }

        $this->DB->update($this->table, $upd, $this->explodePrimaryKey($id));
    }

    /**
     * (Oper) Delete
     *
     * @param  integer|string $id - one or multiple id's to delete
     * @return void
     */
    protected function opDel($id)
    {
        if(empty($this->table))
        {
            throw new jqGrid_Exception('Table is not defined');
        }

        $where = array();

        foreach(explode(',', $id) as $pk)
        {
            $wh = array();

            foreach($this->explodePrimaryKey($pk) as $kk => $vv)
            {
                $wh[] = "$kk = " . $this->DB->quote($vv);
            }

            $where[] = '(' . implode(' AND ', $wh) . ')';
        }

        $this->DB->delete($this->table, implode(' OR ', $where));
    }

    //----------------
    // OPER HOOKS PART
    //----------------

    /**
     * (Oper) Modify form data for opAdd and opEdit only
     * These operations usually need the same modifications, so i made a stand-alone hook for them
     *
     * @param  array $r - form data
     * @return array - modified form data
     */
    protected function operData($r)
    {
        return $r;
    }

    /**
     * (Oper) Hook after opAdd and opEdit
     * Useful for uploading images after processing other data etc.
     *
     * @param $id - id of updated or inserted row
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
     * @param  $oper - operation name
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
        $data = array(
            'page' => $this->page,
            'total' => $this->total,
            'records' => $this->count,
            'rows' => $this->rows,
            'userdata' => $this->userdata,
        );

        if($this->Loader->get('debug_output'))
        {
            $data['debug'] = $this->debug;
        }

        $this->json($data);
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
        $lib = new $class($this->Loader);
        $this->setExportData($lib);
        $lib->doExport();
    }

    /**
     * (Output) Encode each row value before output
     *
     * @param $c - column
     * @param $val - value
     * @return string - encoded value
     */
    protected function outputEncodeValue($c, $val)
    {
        return htmlspecialchars($val, ENT_QUOTES);
    }

    /**
     * (Output) Modify row data before output
     *
     * @param  array $r - row data
     * @return array - modified row data
     */
    protected function parseRow($r)
    {
        return $r;
    }

    //----------------
    // RENDER PART
    //----------------

    /**
     * (Render) Render single column
     *
     * @param array $c - col properties
     * @return array - modified col properties
     */
    protected function renderColumn($c)
    {
        return $c;
    }

    /**
     * Get grid_id suffix when multiple instances are used on one page
     *
     * @param $render_data
     * @return string
     */
    protected function renderGridSuffix($render_data)
    {
        if(isset($this->render_suffix_col) and isset($render_data[$this->render_suffix_col]))
        {
            return jqGrid_Utils::checkAlphanum($render_data[$this->render_suffix_col]);
        }

        return '';
    }

    /**
     * (Render) Base url is used into 'url', 'editurl', 'cellurl' options
     *
     * @return string
     */
    protected function renderGridUrl()
    {
        $params[$this->Loader->get('input_grid')] = $this->grid_id;

        foreach($this->render_data as $k => $v)
        {
            $params[$k] = $v;
        }

        return $this->base_url . '?' . http_build_query($params);
    }

    /**
     * (Render) There are few ways to create basic html markup for jqGrid
     * You may alter this if you want yours
     *
     * @param $data - render data
     * @return string - string to be added before .jqGrid call
     */
    protected function renderHtml($data)
    {
        return '
</script>
<table id="' . $data['id'] . '"></table>
<div id="' . $data['pager_id'] . '"></div>
<script>
';
    }

    /**
     * (Render) Alter 'nav'
     *
     * @param  $nav original nav
     * @return array modified nav
     */
    protected function renderNav($nav)
    {
        return $nav;
    }

    /**
     * (Render) Alter 'options'
     *
     * @param  array $opts original options
     * @return array modified options
     */
    protected function renderOptions($opts)
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
    protected function renderComplete($data)
    {
        $data['extend'] = $data['extend'] ? $data['extend'] : '{}'; //prevent errors on empty 'extend'

        $code = $data['html'] . '

var pager = "#' . $data['pager_id'] . '";

var $grid = $("#' . $data['id'] . '");
var $' . $data['id'] . ' = $grid;

$grid.jqGrid($.extend(' . jqGrid_Utils::jsonEncode($data['options']) . ', typeof(' . $data['extend'] . ') == "undefined" ? {} : ' . $data['extend'] . "));\n";

        #Event binder
        $code .= "\$grid.jqGrid('extBindEvents');\n";

        #NavGrid
        if(isset($data['nav']))
        {
            $nav_special = array('prmEdit', 'prmAdd', 'prmDel', 'prmSearch', 'prmView');
            $code .= "\$grid.jqGrid('navGrid', pager, " . jqGrid_Utils::jsonEncode(array_diff_key($data['nav'], array_flip($nav_special)));

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
                $code .= "\$grid.jqGrid('navButtonAdd', pager, {caption: '{$data['nav']['exceltext']}', title: '{$data['nav']['exceltext']}', icon: 'ui-extlink', onClickButton: function(){ \$(this).jqGrid('extExport', {'export' : 'ExcelHtml', 'rows': -1}); }});\n";
            }
        }

        #Filter toolbar
        if($this->render_filter_toolbar)
        {
            $code .= "\$grid.jqGrid('filterToolbar');\n";
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
     * @return void
     */
    protected function search()
    {
        foreach($this->cols as $k => $c)
        {
            if(!isset($this->input[$k]) or $this->input[$k] === '')
            {
                continue;
            }

            #Preserve original input value
            $val = $this->input[$k];

            if(is_array($val))
            {
                foreach($val as $kk => $vv)
                {
                    jqGrid_Utils::checkAlphanum($kk);
                    $val[$kk] = $this->searchCleanVal($vv);
                }
            }
            else
            {
                $val = $this->searchCleanVal($val);
            }

            //------------------
            // Apply search operator
            //------------------

            $callback = array($this, jqGrid_Utils::uscore2camel('searchOp', $c['search_op']));

            if(!is_callable($callback))
            {
                throw new jqGrid_Exception('Search operation ' . $c['search_op'] . ' is not defined');
            }

            $wh = call_user_func($callback, $c, $val);
            if($wh) $this->where[] = $wh;
        }
    }

    /**
     * (Output) Perform searching based on input json-encoded variable 'filters'
     * Populates $this->where with SQL-expressions
     *
     * @return void
     */
    protected function searchAdvanced()
    {
        $filters = $this->input('filters');

        if(empty($filters))
        {
            return;
        }

        $filters = json_decode($filters, true);

        if(empty($filters))
        {
            return;
        }

        $this->where[] = $this->searchAdvancedGroup($filters);
    }

    /**
     * (Output) Recursive processor for each search group
     *
     * @param $row
     * @return string
     */
    protected function searchAdvancedGroup($row)
    {
        static $base = array(
            'groupOp' => 'AND',
            'rules' => array(),
            'groups' => array(),
        );

        static $basic_ops = array(
            'eq' => '=',
            'ne' => '!=',
            'lt' => '<',
            'le' => '<=',
            'gt' => '>',
            'ge' => '>=',
        );

        static $like_ops = array(
            'bw' => "LIKE '{data}%'",
            'bn' => "NOT LIKE '{data}%'",
            'ew' => "LIKE '%{data}'",
            'en' => "NOT LIKE '%{data}'",
            'cn' => "LIKE '%{data}%'",
            'nc' => "NOT LIKE '%{data}%'",
        );

        $row = array_merge($base, $row);
        $row['groupOp'] = in_array($row['groupOp'], array('AND', 'OR')) ? $row['groupOp'] : 'AND';

        $wh = array();

        //------------
        // Process rules
        //------------

        foreach($row['rules'] as $r)
        {
            if(!array_key_exists($r['field'], $this->cols))
            {
                continue;
            }

            $op = $r['op'];
            $c = $this->cols[$r['field']];
            $data = $this->searchCleanVal($r['data']);

            //-------------
            // Empty data? Skip this rule!
            //-------------

            if(empty($data) and !in_array($op, array('nu', 'nn')))
            {
                continue;
            }

            //-------------
            // Customer search op
            //-------------

            if($c['search_op'] and $c['search_op'] != 'auto')
            {
                $callback = array($this, jqGrid_Utils::uscore2camel('searchOp', $c['search_op']));

                if(!is_callable($callback))
                {
                    throw new jqGrid_Exception('Search operation ' . $c['search_op'] . ' is not defined');
                }

                $wh[] = call_user_func($callback, $c, $data);

                continue;
            }

            //-------------
            // Common search op's
            //-------------

            if(array_key_exists($op, $basic_ops))
            {
                $wh[] = $c['db'] . ' ' . $basic_ops[$op] . " '$data'";
            }
            elseif(array_key_exists($op, $like_ops))
            {
                $wh[] = $c['db'] . ' ' . str_replace('{data}', addcslashes($data, '%_'), $like_ops[$op]);
            }
            else
            {
                switch($op)
                {
                    case 'nu':
                        $wh[] = $c['db'] . ' IS NULL';
                        break;

                    case 'nn':
                        $wh[] = $c['db'] . ' IS NOT NULL';
                        break;

                    case 'in':
                        $wh[] = $c['db'] . " IN ('" . implode("','", array_map('trim', explode(',', $data))) . "')";
                        break;

                    case 'ni':
                        $wh[] = $c['db'] . " NOT IN ('" . implode("','", array_map('trim', explode(',', $data))) . "')";
                        break;
                }
            }
        }

        //------------
        // Process sub-groups recursively
        //------------

        foreach($row['groups'] as $g)
        {
            $wh[] = $this->searchAdvancedGroup($g);
        }

        //------------
        // Implode rules
        //------------

        $wh = array_filter($wh);

        return $wh ? ('(' . implode(' ' . $row['groupOp'] . ' ', $wh) . ')') : $this->where_empty;
    }

    /**
     * (Output) Clean each search value before sending it to other functions
     * Returns clean, but UNQUOTED value!!
     *
     * @param $val - value
     * @return string - clean value
     */
    protected function searchCleanVal($val)
    {
        $val = trim($val);
        $val = $this->DB->quote($val);

        #Strip quotes for easier values handling
        $start = (strpos($val, "E'") === 0) ? 2 : 1;
        $val = substr($val, $start, -1);

        return $val;
    }

    /**
     * (Output) Auto detect of searchOp
     *
     * @param  $c - column settings
     * @param  $val - "clean" value. If you need original value - use $this->input
     * @return string - SQL-expression -> goes directly to WHERE. Set false to skip search.
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
     * (Output) Disable search
     *
     * @param $c - column
     * @param $val - value
     * @return bool
     */
    protected function searchOpIgnore($c, $val)
    {
        return false;
    }

    /**
     * (Output) Look for EXACT match
     *
     * @param $c - column
     * @param $val - value
     * @return string
     */
    protected function searchOpEqual($c, $val)
    {
        return $c['db'] . "	= '$val'";
    }

    /**
     * (Output) Look for similar text
     *
     * @param $c - column
     * @param $val - value
     * @return string
     */
    protected function searchOpLike($c, $val)
    {
        #Escape wildcards
        $val = addcslashes($val, '%_');

        $op = ($this->DB->getType() == 'postgresql') ? 'ILIKE' : 'LIKE';

        return $c['db'] . " $op '%$val%'";
    }

    /**
     * (Output) Look for list of values
     *
     * @param $c - column
     * @param $val - value
     * @return string
     */
    protected function searchOpIn($c, $val)
    {
        $hash = array_map('trim', explode(',', $val));

        return $c['db'] . " IN ('" . implode("','", $hash) . "')";
    }

    /**
     * (Output) Look for numeric value
     * Supports prefixes to search for range of numeric values
     *
     * @param $c - column
     * @param $val - value
     * @return string
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

        $op = isset($op) ? $op : '=';
        $val = floatval(trim($val));

        return $c['db'] . " $op '$val'";
    }

    /**
     * (Output) Send data to export library
     *
     * @param jqGrid_Export $lib
     * @return void
     */
    protected function setExportData(jqGrid_Export $lib)
    {
        $lib->grid_id = $this->grid_id;
        $lib->input = $this->input;
        $lib->DB = $this->DB;

        $lib->cols = $this->cols;
        $lib->rows = $this->rows;
        $lib->userdata = $this->userdata;

        $lib->page = $this->page;
        $lib->total = $this->total;
    }

    /**
     * (Output) Set total row count and calc related vars
     *
     * @throws jqGrid_Exception
     * @param integer $count - set this argument to override auto-detection
     * @return void
     */
    protected function setRowCount($count = null)
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
            $this->count = 0;
            $this->page = 0;
            $this->total = 0;
            $this->offset = 0;

            return;
        }

        $this->count = $count;

        if($this->limit == -1)
        {
            $this->total = 1;
            $this->page = 1;
            $this->offset = 0;
        }
        elseif($this->limit)
        {
            $this->total = ($this->count > 0) ? ceil($this->count / $this->limit) : 0;

            $this->page = ($this->page <= $this->total) ? $this->page : $this->total;
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
     * @param string|array $key - single key or set of keys
     * @param mixed $default - default value in case key does not exist
     * @return mixed
     */
    protected function input($key, $default = null)
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
                header("Content-type: text/html; charset={$this->Loader->get('encoding')};");
                //echo '<textarea>' . jqGrid_Utils::jsonEncode($obj) . '</textarea>';
                echo jqGrid_Utils::jsonEncode($obj);
                break;

            default:
                header("Content-type: application/json; charset={$this->Loader->get('encoding')};");
                echo jqGrid_Utils::jsonEncode($obj);
                break;
        }
    }

    /**
     * Extract primary key from string
     *
     * @param string $primary_key
     * @return array
     * @throws jqGrid_Exception
     */
    protected function explodePrimaryKey($primary_key)
    {
        $pk_parts = explode($this->primary_key_glue, $primary_key);

        if(count($this->primary_key) != count($pk_parts))
        {
            throw new jqGrid_Exception('Incorrect format of primary key');
        }

        return array_combine($this->primary_key, $pk_parts);
    }

    /**
     * Build primary key string
     *
     * @param array $row
     * @return string
     */
    protected function implodePrimaryKey($row)
    {
        foreach($this->primary_key as $k)
        {
            $pk_parts[] = $row[$k];
        }

        return implode($this->primary_key_glue, $pk_parts);
    }
}