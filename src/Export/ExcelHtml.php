<?php
/**
 * Simplest & fastest XLS export
 * Construct HTML table and send it with Excel headers
 * Excel handles most of things for you
 */
class jqGrid_Export_ExcelHtml extends jqGrid_Export
{
    protected $html;

    public function doExport()
    {
        $this->buildTable();
        $this->output($this->grid_id . '.xls');
    }

    /**
     * We dont use DOMDocument here to make table generation as fast as possible
     * You can see the difference when working with thousands of rows
     */
    protected function buildTable()
    {
        $t = '<table border="1">';

        //--------------
        // Columns
        //--------------

        $t .= '<tr>';
        foreach($this->cols as $k => $c)
        {
            if($c['hidden'] or $c['unset']) continue;

            $t .= '<th>' . $c['label'] . '</th>';
        }

        $t .= '</tr>';

        //--------------
        // Rows
        //--------------

        foreach($this->rows as $r)
        {
            $i = -1; //cell index

            $t .= '<tr>';

            foreach($this->cols as $k => $c)
            {
                if($c['unset']) continue;

                $i++;

                if($c['hidden']) continue;

                $val = $r['cell'][$i];

                if(is_numeric($val)) $val = str_replace('.', ',', $val);

                $t .= '<td align="' . $c['align'] . '">' . $val . '</td>';
            }

            $t .= '</tr>';
        }

        if(isset($this->userdata['agg']) and array_intersect_key($this->cols, $this->userdata['agg']))
        {
            $t .= '<tr>';
            foreach($this->cols as $k => $c)
            {
                if($c['hidden']) continue;
                $t .= '<td align="' . $c['align'] . '"><b>' . (isset($this->userdata['agg'][$k]) ? str_replace('.', ',', $this->userdata['agg'][$k]) : '') . '</b></td>';
            }
            $t .= '</tr>';
        }

        $t .= '</table>';

        $this->html = $t;
    }

    public function output($filename, $path = 'php://output')
    {
        //--------------
        // Headers
        //--------------

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename;");

        //--------------
        // Content
        //--------------

        $content = <<<EOF
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset={$this->Loader->get('encoding')}" />
</head>
<body>
	{$this->html}
</body>
</html>
EOF;
        file_put_contents($path, $content);
    }
}