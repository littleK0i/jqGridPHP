<?php

#Directory separator shotrcut
if(!defined('DS'))
{
    define('DS', DIRECTORY_SEPARATOR);
}

class jqGridLoader
{
	protected $root_path;
	protected $grid_path;

	protected $settings = array(
		'grid_path'	   => null,
		'encoding'     => 'utf-8',

		'db_driver'	   => 'pdo',

		'pdo_dsn'      => null,
		'pdo_user'     => 'root',
		'pdo_pass'     => '',
		'pdo_options'  => null,

		'debug_output' => false,

		'input_grid'   => 'jqgrid',
		'input_oper'   => 'oper',
	);
	
	public function __construct()
	{
		#Root_path
		$this->root_path = dirname(__FILE__) . DS;
		$this->settings['grid_path'] = $this->root_path . 'grids' . DS;

		#Load base grid class
		require_once($this->root_path . 'jqGrid.class.php');

		#Load everything from utils
		foreach(glob($this->root_path . DS . 'utils' . DS . '*.class.php') as $f)
		{
			require_once $f;
		}

		#Register autoload
		spl_autoload_register(array($this, 'autoload'), false, true);
	}

	/**
	 * Access grid public methods via this function
	 * $jq_loader->render('jq_example');
	 */
	public function __call($func, $arg)
	{
		$grid = $this->load($arg[0]);
		unset($arg[0]);

		try
		{
			return call_user_func_array(array($grid, $func), $arg);
		}
		catch(Exception $e)
		{
			$grid->catchException($e);
		}
	}

	public function loadAdaptor($name)
	{
		//require_once($this->root_path . 'adaptor' . DS . $name . '.class.php');
	}

	public function loadExport($name)
	{
		$name = 'jqGrid_Export_'.ucfirst($name);
		return new $name($this);
	}

	public function set($key, $val)
	{
		$this->settings[$key] = $val;
	}

	public function get($key)
	{
		return isset($this->settings[$key]) ? $this->settings[$key] : null;
	}

	public function getDB()
	{
		$class = 'jqGrid_DB_' . ucfirst($this->settings['db_driver']);

		//require_once $this->root_path . DS . 'db' . DS . 'jqGrid_DB.class.php';
		//require_once $this->root_path . DS . 'db' . DS . $class . '.class.php';

		return new $class($this);
	}

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
			require $this->root_path . $parts[1] . DS . $parts[1] . '.php';
		}
		#Extend class
		else
		{
			require $this->root_path . implode(DS, array_slice($parts, 1, -1)) . DS . end($parts)  . '.php';
		}
	}

	protected function load($name)
	{
		require_once($this->settings['grid_path'] . $name . '.php');
		$grid = new $name($this);

		return $grid;
	}
}