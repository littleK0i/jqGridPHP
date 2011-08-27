<?php

abstract class jqGrid_Adapter_ReadRu extends jqGrid
{
	protected $request;
	protected $output;
	
	public function output()
	{
		parent::output();
		exit;
	}

	public function dialog($dialog)
	{
		$this->output->setTemplate(SECTION . '/' . $this->grid_id . '/dg_'.$dialog);

		$this->output->toTemplate('dg_action', $this->renderGridUrl(), true);

		if(is_callable(array($this, 'dg'.ucfirst($dialog))))
		{
			call_user_func(array($this, 'dg'.ucfirst($dialog)));
		}

		$this->output->display_ajax();
		exit;
	}

	public function catchException(jqGrid_Exception $e)
	{
		if($e instanceof jqGrid_Exception_Render)
		{
			return parent::catchException($e) . "\n"
			. '$grid = $("<table>"); pager = null; $.extend(null, {';
		}

		return parent::catchException($e);
	}

	protected function beforeInit()
	{
		$this->default['cols']['null'] = '';
		$this->output = output::getInstance();

		$this->reserved_col_names = array_merge($this->reserved_col_names, array('section', 'act', 'module', 'oper'));
	}

	protected function afterInit()
	{
		if(isset($this->tblName))
		{
			$this->table = $this->tblName;
		}
	}

	protected function request($k, $default=null)
	{
		return $this->input($k, $default);
	}

	protected function initColumn($k, $c)
	{
		$c = parent::initColumn($k, $c);

		$classes = 'cell-align-'.$c['align'];
		$c['classes'] .= ($c['classes'] ? ' ' : '') . $classes;

		return $c;
	}

	protected function renderGridUrl()
	{
		$vars = array('section' => SECTION, 'act' => $this->grid_id);
		return '?' . http_build_query($vars);
	}

	protected function renderComplete($data)
	{
		return '
			</script>
			<!-- Grid base -->
			<table id="'.$data['id'].'"></table>
			<div id="'.$data['pager_id'].'"></div>

			<!-- Grid JS -->
			<script>
			var grid = "#'.$data['id'].'";
			var pager = "#'.$data['pager_id'].'";

			var $grid = $(grid);
			var $pager = $(pager);

			var $'.$data['id'].' = $grid;

			$grid.jqGrid('.substr(jqGrid_Utils::jsonEncode($data['options']), 0, -2) . ', ';
	}

	protected function getInput()
	{
		$this->request = coreInput::getCleanInput();
		return $this->request;
	}

	protected function getInputDate($k)
	{
		if(isset($this->request[$k]) and is_array($this->request[$k]) and count($this->request[$k]) == 2)
		{
			$date = array_map('pg_escape_string', $this->request[$k]);
		}
		else
		{
			//$date = array(date('01.m.Y'), date('d.m.Y'));
			$date = array('01.01.1970', '01.01.2020');
		}

		return $date;
	}

	protected function getInputDateRange($k, $bonus_day=false, $default='today')
	{
		$parts = explode('-', $this->request($k));
		$parts[1] = isset($parts[1]) ? $parts[1] : $parts[0];

		$parts = preg_replace('#\.(\d{2})\s*$#', '.20$1', $parts);

		$parts[0] = strtotime($parts[0]);
		$parts[1] = strtotime($parts[1]);

		$parts[0] = $parts[0] ? $parts[0] : ($default == 'all' ? 1 : strtotime('today'));
		$parts[1] = $parts[1] ? $parts[1] : ($default == 'all' ? strtotime('+3 year') : strtotime('today'));

		if($bonus_day) $parts[1] += 86400;

		$parts[0] = date('d.m.Y', $parts[0]);
		$parts[1] = date('d.m.Y', $parts[1]);

		return array($parts[0], $parts[1]);

		//return "'{$parts[0]}' AND '{$parts[1]}'";
	}

	protected function searchOpAny($c, $val)
	{
		return "'$val'=ANY({$c['db']})";
	}

	protected function toTemplate($key, $val, $no_clean=false)
	{
		$this->output->toTemplate($key, $val, $no_clean);
	}

	protected function filterKeys($hash)
	{
		return array_diff_key($hash, array_flip($this->reserved_col_names));
	}
}