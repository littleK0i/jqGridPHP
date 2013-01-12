<?php

abstract class jqGrid_Export
{
    /** @var $Loader jqGridLoader */
    protected $Loader;

    public $input;
    public $grid_id;

    public $cols;
    public $rows;
    public $userdata;

    public $page;
    public $total;
    public $records;

    public function __construct(jqGridLoader $loader)
    {
        $this->Loader = $loader;
    }

    abstract public function doExport();
}