<?php

class jqOperUpload extends jqGrid
{
	protected function init()
	{
		$this->file_ext = array('jpg' => 'jpg', 'gif' => 'gif', 'png' => 'png');
		$this->img_path = $this->loader->get('grid_path') . $this->grid_id . DS;

		$this->table = 'tbl_files';

		$this->cols_default = array('align' => 'center');

		#Set columns
		$this->cols = array(

			'id'		=>array('label' => 'ID',
								'width' => 10,
								'formatter' => 'integer',
								),

			'image'		=>array('label' => 'Image',
								'width' => 14,
								'manual' => true, //manually create images in PHP
				 				                  //you can use JS formatter instead
								'encode' => false,
								),
			
			'filename'  =>array('label' => 'Filename',
								'width' => 25,
								'align' => 'center',
								),

			'file_ext'  =>array('label'=> 'Extension',
								'db'   => 'SUBSTRING(filename FROM -3 FOR 3)', //last 3 chars
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
								'manual' => true,
								'hidden' => true,
								'editable' => true,
								'edittype' => 'file',
								'editrules' => array('edithidden' => true),
								'formoptions' => array('elmsuffix' => '&nbsp;&nbsp;&nbsp;' . implode(', ', $this->file_ext)),
								),

			'comment'	=>array('label' => 'Comment',
								'width' => 50,
								'align' => 'left',
								'editable' => true,
								'edittype' => 'textarea',
								'editoptions' => array('style' => 'height: 120px; width: 260px;'),
								),

			'version'  =>array('unset' => true),
		);

		$nav_prm = array('width' => '420');

		$this->nav = array('add' => true, 'edit' => true, 'addtext' => 'Add', 'edittext' => 'Edit', 'prmAdd' => $nav_prm, 'prmEdit' => $nav_prm);
	}

	protected function parseRow($r)
	{
		$r['image'] = "<img src='grids/{$this->grid_id}/{$r['id']}.{$r['file_ext']}?v={$r['version']}'>";
		
		return $r;
	}

	#Init file processing
	protected function operData($r)
	{
		if(isset($_FILES['upload']))
		{
			require_once 'misc/upload.class.php';

			$this->upload = new upload($_FILES['upload']);

			if(!$this->upload->uploaded)
			{
				throw new jqGrid_Exception('Upload failed');
			}

			if(!in_array($this->upload->file_src_name_ext, $this->file_ext))
			{
				throw new jqGrid_Exception('Bad file type');
			}

			$r['filename'] = $this->upload->file_src_name;
			$r['size']     = $this->upload->file_src_size;
		}

		return $r;
	}

	protected function opEdit($id, $upd)
	{
		$upd['version'] = new jqGrid_Data_Raw('version + 1');

		return parent::opEdit($id, $upd);
	}

	#Upload
	protected function operAfterAddEdit($id)
	{
		if(isset($_FILES['upload']))
		{
			ini_set('memory_limit', '128M');
			
			$this->upload->file_new_name_body   = $id;
			$this->upload->file_auto_rename     = false;
			$this->upload->file_overwrite       = true;
			$this->upload->image_resize         = true;
			$this->upload->image_x              = 75;
			$this->upload->image_y        		= 75;
			$this->upload->image_ratio_crop		= true;

			$this->upload->process($this->img_path);
		}
	}
}