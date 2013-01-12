<?php
/**
 * Special serialization for 'selects'
 * and 'searchoptions' => 'value', 'editoptions' => 'value' etc.
 */
class jqGrid_Data_Value extends jqGrid_Data
{
    #Ensure 'array' input
    public function __construct(array $data, $first = null)
    {
        if(!is_null($first))
        {
            $data = array('' => $first) + $data;
        }

        parent::__construct($data);
    }

    public function __toString()
    {
        $base = array();

        foreach($this->data as $k => $v)
        {
            $base[] = $k . ':' . $v;
        }

        return '"' . implode(';', $base) . '"';
    }
}