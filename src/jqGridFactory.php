<?php
namespace jqGridPHP;

class jqGridFactory
{
    /**
     * @param $grid_class
     * @param array $render_data
     * @return string
     * @throws jqException
     */
    public function render($grid_class, array $render_data = array())
    {
        $Grid = $this->getGridInstance($grid_class);
        return $Grid->actRender($render_data);
    }

    /**
     * @param array $input
     * @throws jqException
     */
    public function dispatch(array $input)
    {
        $grid_class = isset($input['jqgrid']) ? $input['jqgrid'] : null;

        try {
            $Grid = $this->getGridInstance($grid_class);

            if (isset($input['oper'])) {
                $Grid->actOper($input['oper']);
            } else {
                $Grid->actOutput();
            }
        } catch (jqException $e) {
            if (isset($Grid)) {
                $Grid->outputException($e);
            } else {

            }
        }
    }

    /**
     * @param $grid_class
     * @return jqGrid
     * @throws jqException
     */
    protected function getGridInstance($grid_class)
    {
        if (!class_exists($grid_class)) {
            throw new jqException("Grid $grid_class not found");
        }

        if (!is_subclass_of($grid_name, jqGrid::CLASS)) {
            throw new jqException("Grid $grid_class is not subclass of base jqGrid class");
        }

        $Grid = new $grid_class();

        return $Grid;
    }
}
