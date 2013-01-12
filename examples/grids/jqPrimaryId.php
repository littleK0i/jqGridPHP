<?php

class jqPrimaryId extends jqGrid
{
    protected function init()
    {
        #Set table
        $this->table = 'tbl_customer';

        #Set columns
        $this->cols = array(
            'id' => array(
                'label' => 'ID',
                'width' => 10,
                'align' => 'center',
                'editable' => true,
            ),

            'first_name' => array(
                'label' => 'First name',
                'width' => 35,
                'editable' => true,
                'editrules' => array('required' => true),
            ),

            'last_name' => array(
                'label' => 'Last name',
                'width' => 35,
                'editable' => true,
                'editrules' => array('required' => true),
            ),

            'email' => array(
                'label' => 'Email',
                'width' => 30,
                'editrules' => array('email' => true),
            ),

            'phone' => array(
                'label' => 'Phone',
                'width' => 25,
                'align' => 'center',
            ),

            'discount' => array(
                'label' => 'Discount',
                'width' => 15,
                'formatter' => 'numeric',
                'align' => 'center',
            ),
        );

        #Set nav
        $this->nav = array('add' => true, 'edit' => true, 'del' => true);
    }
}