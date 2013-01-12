<?php

abstract class jqGrid_Adapter_CSV extends jqGrid
{
    protected $csv;

    protected function beforeInit()
    {
        $this->do_search = false;
        $this->do_agg = false;

        $this->tblName = '_empty'; //prevent warnings!
    }

    protected function afterInit()
    {
        if(!$this->csv)
        {
            throw new jqGridException("Variable 'csv' must be defined!");
        }
    }

    protected function getDataRows()
    {
        foreach($this->readCSV() as $r)
        {
            $this->addRow($r);
        }
    }

    protected function opAdd($ins)
    {
        $rows = $this->readCSV();
        $last_id = end(array_keys($rows));

        $new_id = $last_id + 1;

        $new_row = array($this->primary_key[0] => $new_id);

        #Preserve column order!
        foreach($this->cols as $k => $c)
        {
            if(!$c['manual'] and $k != $this->primary_key[0])
            {
                $new_row[$k] = isset($ins[$k]) ? $ins[$k] : null;
            }
        }

        $rows[$new_id] = $new_row;

        $this->writeCSV($rows);
    }

    protected function opEdit($id, $upd)
    {
        $rows = $this->readCSV();

        foreach($rows[$id] as $k => &$v)
        {
            if(isset($upd[$k]))
            {
                $v = $upd[$k];
            }
        }

        $this->writeCSV($rows);
    }

    protected function opDel($ids)
    {
        $rows = $this->readCSV();

        foreach(explode(',', $ids) as $id) //ensure multiselect support
        {
            unset($rows[$id]);
        }

        $this->writeCSV($rows);
    }

    protected function readCSV()
    {
        $file = fopen($this->csv, 'rb');
        $keys = array_keys($this->cols);

        $rows = array();

        while($r = fgetcsv($file))
        {
            $r = array_combine($keys, $r);
            $rows[$r[$this->primary_key[0]]] = $r;
        }

        fclose($file);

        return $rows;
    }

    protected function writeCSV(array $rows)
    {
        $file = fopen($this->csv, 'wb');

        foreach($rows as $r)
        {
            fputcsv($file, $r);
        }

        fclose($file);

        return true;
    }
}