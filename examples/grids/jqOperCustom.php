<?php

class jqOperCustom extends jqGrid
{
    protected function init()
    {
        $this->options = array('multiselect' => true,);

        $this->table = 'tbl_books';

        #Set columns
        $this->cols = array(
            'id' => array('label' => 'ID',
                'width' => 10,
                'align' => 'center',
                'formatter' => 'integer',
            ),

            'name' => array('label' => 'Name',
                'width' => 40,
            ),

            'price' => array('label' => 'Price',
                'width' => 15,
                'formatter' => 'integer',
            ),
        );
    }

    protected function opPrice()
    {
        $price = intval($this->input('price'));

        if($price < 1 or $price > 3000)
        {
            throw new jqGrid_Exception('Incorrect price!');
        }

        foreach($this->input['id'] as $id)
        {
            $this->DB->update($this->table, array('price' => $price), intval($id));
        }
    }
}