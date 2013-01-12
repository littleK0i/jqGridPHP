<?php

class jqRender2 extends jqGrid
{
    protected function init()
    {
        $this->nav = array(

            #Set common nav actions
            'edit' => true,
            'del' => true,
            'view' => true,

            #Set text labels. It's better to set them in defaults
            'edittext' => 'Edit',
            'deltext' => 'Delete',
            'viewtext' => 'View',

            #Set common excel export
            'excel' => true,
            'exceltext' => 'Excel',

            #Set editing params
            'prmEdit' => array('width' => 400,
                'bottominfo' => 'Custom info came from PHP!',
                'viewPagerButtons' => false),
        );

        $this->table = 'tbl_customer';

        $this->cols = array(

            'id' => array('label' => 'ID',
                'width' => 10,
                'align' => 'center',
            ),

            'first_name' => array('label' => 'First name',
                'width' => 35,
                'editable' => true,
                'editrules' => array('required' => true),
            ),

            'last_name' => array('label' => 'Last name',
                'width' => 35,
                'editable' => true,
                'editrules' => array('required' => true),
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

    protected function renderNav($nav)
    {
        #Disable 'del' depending on condition
        if(mt_rand(1, 10) > 5)
        {
            $nav['del'] = false;
        }

        return $nav;
    }
}