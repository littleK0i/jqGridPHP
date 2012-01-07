<?php

class jqOutTreeFull extends jqGrid
{
	protected $do_count = false;
	
	protected function init()
	{
		#Set tree grid mode
		$this->treegrid = 'adjacency';
	
		#Set database table
		$this->table = 'tbl_tree';
		
		#Set condition for base level
		$this->where = array('t.parent_id=0');
		
		$this->query = "
			SELECT {fields}
			FROM tbl_tree t
			WHERE {where}
		";

		#Set columns
		$this->cols = array(
			
			'id'        =>array('label' => 'ID',
								'db'    => 't.id',
								'width' => 8,
								'align' => 'center',
								),
						
			'parent_id'	=>array('hidden' => true,
								'db'     => 't.parent_id',
								),

			'node_name' =>array('label' => 'Node name',
								'db'    => 't.node_name',
								'width'	=> 55,
								),

			'price'		=>array('label' => 'Price',
								'db'    => 't.price',
								'width'	=> 15,
								),
		);
	}
	
	protected function addRow($orig_row, $parent = 0, $level = 0)
	{
		#Set new condition for query builder
		$this->where = array('t.parent_id=' . intval($orig_row['id']));
		
		#Get children of current node
		$query = $this->buildQueryRows($this->query);
		$result = $this->DB->query($query);
		
		#Add current node
		$orig_row['level'] = $level;
		$orig_row['parent'] = $parent ? $parent : null;
		$orig_row['isLeaf'] = $this->DB->rowCount($result) ? false : true;
		$orig_row['expanded'] = $this->input('expanded') ? true : false;
		$orig_row['loaded'] = true;
		
		parent::addRow($orig_row);
		
		#Add children nodes recursively
		while($r = $this->DB->fetch($result))
		{
			$this->addRow($r, $orig_row['id'], $level + 1);
		}
	}
	
	protected function renderPostData()
	{
		$p['expanded'] = $this->input('expanded') ? 1 : 0;
		return $p;
	}
}