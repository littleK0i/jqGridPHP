<?php

class jqPrimaryMulti extends jqGrid
{
    protected function init()
    {
        #Set table
        $this->table = 'bnd_customer_country';

        #Set primary key
        $this->primary_key = array('customer_id', 'country_name');

        #Add a random join
        $this->query = "
            SELECT {fields}
            FROM {$this->table} n
                JOIN tbl_customer c ON (n.customer_id=c.id)
            WHERE {where}
        ";

        #Set columns
        $this->cols = array(
            'customer_id' => array(
                'label' => 'Customer ID',
                'db' => 'n.customer_id',
                'width' => 20,
                'align' => 'center',
            ),

            'first_name' => array(
                'label' => 'First name',
                'db' => 'c.first_name',
                'width' => 35,
            ),

            'last_name' => array(
                'label' => 'Last name',
                'db' => 'c.last_name',
                'width' => 35,
            ),

            'country_name' => array(
                'label' => 'Country name',
                'db' => 'n.country_name',
                'width' => 35,
            ),

            'value' => array(
                'label' => 'Value',
                'db' => 'n.value',
                'width' => 20,
                'editable' => true,
                'editrules' => array('required' => true, 'integer' => true),
            ),
        );

        #Set nav
        $this->nav = array('edit' => true, 'edittext' => 'Edit');
    }
}