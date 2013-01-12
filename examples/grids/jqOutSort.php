<?php

class jqOutSort extends jqGrid
{
    protected function init()
    {
        $this->table = 'tbl_customer';

        $this->cols = array(

            'id' => array('label' => 'ID',
                'width' => 10,
                'align' => 'center',
            ),

            'first_name' => array('label' => 'First name',
                'width' => 35,
            ),

            'last_name' => array('label' => 'Last name',
                'width' => 35,
            ),

            'email' => array('label' => 'Email',
                'width' => 30,
            ),

            'phone' => array('label' => 'Phone',
                'width' => 25,
                'align' => 'center',
            ),

            'discount' => array('label' => 'Discount',
                'width' => 15,
                'formatter' => 'numeric',
                'align' => 'center',
            ),
        );
    }

    protected function buildOrderBy($sidx, $sord)
    {
        #Special sorting for column 'discount'
        if($sidx == 'discount')
        {
            return "
				ORDER BY
					(CASE WHEN discount = 0.1 THEN 0 ELSE 1 END) $sord,
					$sidx $sord,
					first_name $sord,
					last_name $sord
			";
        }

        return parent::buildOrderBy($sidx, $sord);
    }
}