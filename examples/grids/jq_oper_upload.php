<?php

class jq_oper_upload extends jqGrid
{
	protected function init()
	{
		$this->file_ext = array('jpg' => 'jpg', 'gif' => 'gif', 'png' => 'png');

		$this->table = 'tbl_files';

		$this->cols_default = array('align' => 'center');

		#Set columns
		$this->cols = array(

			'id'		=>array('label' => 'ID',
								'width' => 10,
								'formatter' => 'integer',
								),

			'image'		=>array('label' => 'Image',
								'width' => 30,
								'manual' => true, //manually create images in PHP
				 				                  //you can use JS formatter instead
								),
			
			'filename'  =>array('label' => 'Filename',
								'width' => 25,
								'align' => 'center',
								),

			'file_ext'  =>array('label'=> 'Extension',
								'db'   => 'UPPER(SUBSTRING(filename FROM -3 FOR 3))', //last 3 chars
								'width' => 10,
								'stype' => 'select',
								'searchoptions' => array(
										//object preserves array order in Chrome and Opera
										//if u dont need it - use plain array
										'value' => new jqGrid_Data_Value($this->file_ext, 'All')
									),
								),

			'size'		=>array('label' => 'Size',
								'db'	=> 'size / 1024',
								'width' => 15,
								'formatter' => 'numeric',
								'formatoptions' => array('suffix' => 'Kb'),
								),

			#Hidden column for uploading
			'upload'	=>array('label' => 'Upload image',
								'hidden' => true,
								'editable' => true,
								'edittype' => 'file',
								'editrules' => array('edithidden' => true),
								'formoptions' => array('elmsuffix' => '&nbsp;&nbsp;&nbsp;' . implode(', ', $this->file_ext)),
								),

			'descr'		=>array('label' => 'Description',
								'width' => 50,
								'align' => 'left',
								'editable' => true,
								'edittype' => 'textarea',
								'editoptions' => array('style' => 'height: 120px; width: 260px;'),
								),
		);

		$nav_prm = array('width' => '420');

		$this->nav = array('add' => true, 'edit' => true, 'addtext' => 'Add', 'edittext' => 'Edit', 'prmAdd' => $nav_prm, 'prmEdit' => $nav_prm);
	}
}