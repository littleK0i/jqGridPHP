<?php
/**
 * Special serialization for 'selects'
 * and 'searchoptions' => 'value', 'editoptions' => 'value' etc.
 */

namespace jqGridPHP\Container;

class Value extends AbstractContainer
{
    public function __construct(array $data, $first = null)
    {
        if (!is_null($first)) {
            $data = array('' => $first) + $data;
        }

        parent::__construct($data);
    }

    public function __toString()
    {
        $base = array();

        foreach ($this->data as $k => $v) {
            $base[] = $k . ':' . $v;
        }

        return '"' . implode(';', $base) . '"';
    }
}
