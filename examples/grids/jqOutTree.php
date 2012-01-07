<?php

class jqOutTree extends jqGrid
{
	protected $do_count = false;
	
	protected function init()
	{
		#Set tree grid mode
		$this->treegrid = 'adjacency';
	
		#Set database table
		$this->table = 'tbl_tree';
		
		$this->level     = intval($this->input('n_level', 0));
		$this->parent_id = intval($this->input('nodeid', 0));
		
		$this->query = "
			SELECT {fields}
			FROM tbl_tree t
			WHERE {where} AND t.parent_id='{$this->parent_id}'
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
								
			'has_child' =>array('hidden' => true,
								//do we have any children?
								'db'     => '(SELECT id FROM tbl_tree WHERE parent_id=t.id LIMIT 1)',
								),
		);
	}
	
	protected function parseRow($r)
	{
		#Fields required to build tree grid
		$r['level']    = $this->input('nodeid') ? ($this->level + 1) : 0;
		$r['parent']   = $r['parent_id']; 
		$r['isLeaf']   = $r['has_child'] ? false : true;
		$r['expanded'] = false;
	
		return $r;
	}
}