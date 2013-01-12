<?php

class jqMiscDatepickers extends jqGrid
{
    protected function init()
    {
        $this->table = 'tbl_customer';

        #Set columns
        $this->cols = array(
            'id' => array('label' => 'ID',
                'width' => 10,
                'align' => 'center',
            ),

            'c_name' => array('label' => 'Customer name',
                'db' => "CONCAT(first_name, ' ', last_name)",
                'width' => 35,
            ),

            'date_birth' => array('label' => 'Birth date',
                'width' => 20,
                'searchoptions' => array('dataInit' => $this->initDatepicker(array(
                    'dateFormat' => 'yy-mm-dd',
                    'changeMonth' => true,
                    'changeYear' => true,
                    'minDate' => '1950-01-01',
                    'maxDate' => '2000-01-01',
                    'onSelect' => new jqGrid_Data_Raw('function(){$grid[0].triggerToolbar();}'),
                ))),
                'search_op' => 'equal',
            ),

            'date_register' => array('label' => 'Register date',
                'width' => 20,
                'searchoptions' => array('dataInit' => $this->initDateRangePicker(array(
                    'earliestDate' => '2011-01-01',
                    'latestDate' => '2011-06-10',
                    'dateFormat' => 'yy-mm-dd',
                    'onChange' => new jqGrid_Data_Raw('dateRangePicker_onChange'),
                    'presetRanges' => array(
                        array('text' => 'January 2011', 'dateStart' => '2011-01-01', 'dateEnd' => '2011-02-01'),
                        array('text' => 'February 2011', 'dateStart' => '2011-02-01', 'dateEnd' => '2011-03-01'),
                    ),
                    'datepickerOptions' => array(
                        'changeMonth' => true,
                        'dateFormat' => 'yy-mm-dd',
                        'minDate' => '2011-01-01',
                        'maxDate' => '2011-06-10',
                    ),
                ))),
                'search_op' => 'date_range',
            ),
        );

        $this->render_filter_toolbar = true;
    }

    protected function searchOpDateRange($c, $val)
    {
        //--------------
        // Date range
        //--------------

        if(strpos($val, ' - ') !== false)
        {
            list($start, $end) = explode(' - ', $val, 2);

            $start = strtotime(trim($start));
            $end = strtotime(trim($end));

            if(!$start or !$end)
            {
                throw new jqGrid_Exception('Invalid date format');
            }

            #Stap dates if start is bigger than end
            if($start > $end)
            {
                list($start, $end) = array($end, $start);
            }

            $start = date('Y-m-d', $start);
            $end = date('Y-m-d', $end);

            return $c['db'] . " BETWEEN '$start' AND '$end'";
        }

        //------------
        // Single date
        //------------

        $val = strtotime(trim($val));

        if(!$val)
        {
            throw new jqGrid_Exception('Invalid date format');
        }

        $val = date('Y-m-d', $val);

        return "DATE({$c['db']}) = '$val'";
    }

    protected function initDatepicker($options = null)
    {
        $options = is_array($options) ? $options : array();

        return new jqGrid_Data_Raw('function(el){$(el).datepicker(' . jqGrid_Utils::jsonEncode($options) . ');}');
    }

    protected function initDateRangePicker($options = null)
    {
        $options = is_array($options) ? $options : array();

        return new jqGrid_Data_Raw('function(el){$(el).daterangepicker(' . jqGrid_Utils::jsonEncode($options) . ');}');
    }
}