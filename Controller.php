<?php
namespace frame;

abstract class Controller
{
    public $view;

    public function __construct()
    {
        $this->view = new stdClass();
    }

    public function __get($name)
    {
        if(isset($this->view->$name)) {
            return $this->view->$name;
        }

        return;
    }

    public function render($viewPath, $moduleName='', $action='')
    {
        # allow view to be traced
        $this->view->viewName = "view_{$moduleName}_$action";

        ob_start();
        include  $viewPath;
        return ob_get_clean();
    }
}
