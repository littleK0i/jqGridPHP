<?php

class jqGridLoader
{
    protected $root_path;
    protected $grid_path;

    protected $settings = array(
        'grid_path' => null,
        'encoding' => 'utf-8',

        'db_driver' => 'Pdo',

        'pdo_dsn' => null,
        'pdo_user' => 'root',
        'pdo_pass' => '',
        'pdo_options' => null,

        'debug_output' => false,

        'input_grid' => 'jqgrid',
        'input_oper' => 'oper',
    );

    protected $init_query = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->checkEnvironment();

        #Root_path
        $this->root_path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $this->settings['grid_path'] = $this->root_path . 'grids' . DIRECTORY_SEPARATOR;

        #Load base grid class
        require_once($this->root_path . 'jqGrid.php');

        #Register autoload
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Access grid public methods
     * Like this: $jq_loader->render('jq_example');
     *
     * @param $func - method name
     * @param $arg - arguments
     * @return mixed
     */
    public function __call($func, $arg)
    {
        try
        {
            $grid = $this->load($arg[0]);
            unset($arg[0]);

            return call_user_func_array(array($grid, $func), $arg);
        }
        catch(jqGrid_Exception $e)
        {
            #Grid internal exception
            if(isset($grid))
            {
                return $grid->catchException($e);
            }
            #Loader exception
            else
            {
                return $e;
            }
        }
    }

    /**
     * Set setting
     *
     * @param $key
     * @param $val
     * @return void
     */
    public function set($key, $val)
    {
        $this->settings[$key] = $val;
    }

    /**
     * Get setting value by key
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : null;
    }

    /**
     * Creates new instance of grid class
     *
     * @param $name - grid class name
     * @return object
     */
    public function load($name)
    {
        $file = $this->settings['grid_path'] . $name . '.php';

        if(!is_file($file))
        {
            throw new jqGrid_Exception_Render($name . ' not found!');
        }

        require_once $file;
        return new $name($this);
    }

    /**
     * Loads specific DB class
     * @return object
     */
    public function loadDB()
    {
        $class = 'jqGrid_DB_' . ucfirst($this->settings['db_driver']);
        $lib = new $class($this);

        foreach($this->init_query as $q)
        {
            $lib->query($q);
        }

        return $lib;
    }

    /**
     * Add query to be executed right after database connection
     *
     * @param $query
     * @return void
     */
    public function addInitQuery($query)
    {
        $this->init_query[] = strval($query);
    }

    /**
     * Reset init queries - in case you have to work with multiple connections
     *
     * @return void
     */
    public function resetInitQuery()
    {
        $this->init_query = array();
    }

    /**
     * Basic controller-like function
     * Fell free to replace it with your own code
     *
     * @return void
     */
    public function autorun()
    {
        $name = isset($_REQUEST[$this->settings['input_grid']]) ? $_REQUEST[$this->settings['input_grid']] : '';

        if($name)
        {
            if(isset($_REQUEST[$this->settings['input_oper']]))
            {
                $this->oper($name, $_REQUEST[$this->settings['input_oper']]);
            }
            else
            {
                $this->output($name);
            }

            exit;
        }
    }

    /**
     * jqGridPHP Autoloader
     * It will process only class names starting with 'jqGrid_'
     * @param $class - class name
     * @return void
     */
    protected function autoload($class)
    {
        #Not a jqGrid class
        if(strpos($class, 'jqGrid_') !== 0)
        {
            return;
        }

        $parts = explode('_', $class);

        #Root class
        if(count($parts) == 2)
        {
            $path = $this->root_path . $parts[1] . DIRECTORY_SEPARATOR . $parts[1] . '.php';
        }
        #Extend class
        else
        {
            $path = $this->root_path . implode(DIRECTORY_SEPARATOR, array_slice($parts, 1)) . '.php';
        }

        #Do not interfere with other autoloads
        if(file_exists($path))
        {
            require $path;
        }
    }

    /**
     * Check php environment to determine possible problems
     */
    protected function checkEnvironment()
    {
        if(version_compare(PHP_VERSION, '5.2.0', '<'))
        {
            trigger_error('You need at least PHP 5.2 to run jqGridPHP', E_USER_ERROR);
        }
    }
}