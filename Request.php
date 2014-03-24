<?php
namespace frame;

class Request
{
    public $page;
    public $controllerType;
    public $module;
    public $action;
    public $params;
    public $id;

    public function __construct()
    {
        $this->parseUrl();
    }

    private function getUrlParts()
    {
        $requestNoParam = $_SERVER['REQUEST_URI'];
        $index = strpos($requestNoParam, '?');
        if ($index !== false) {
            $requestNoParam = substr($requestNoParam, 0, $index);
        }

        $requestNoParam = trim($requestNoParam, '/');
        $parts = explode('/', $requestNoParam);
        return $parts;
    }

    private function addPartsToGet($parts)
    {
        $getParams = [];
        while (isset($parts[0])) {
            $param = array_shift($parts);

            if (isset($parts[0])) {
                $getParams[$param] = array_shift($parts);
            } else {
                if ($param !== '') {
                    $getParams[$param] = '';
                }
            }
        }

        # allow parameters to be override in order given
        foreach ($_GET as $p => $v) {
            $getParams[$p] = $v;
        }
        $_GET = $getParams;
    }

    /**
     * This method can be overridden if url should be parse differently
     */
    protected function parseUrl()
    {
        $this->controllerType = 'Index';
        # for non-index controllers
        $this->page = 'index';
        $this->module = null;
        $this->action = null;

        $parts = $this->getUrlParts();

        if (in_array($parts[0], ['form', 'async'])) {
            if (count($parts) < 3) {
                echo 'bad url';
                exit;
            }

            $this->controllerType = strtolower($parts[0]);
            $this->module = $parts[1];
            $this->action = $parts[2];

            $parts = array_slice($parts, 3);
        }
        else {
            # Normal Index Controller
            if (count($parts) >= 1) {
                if ($parts[0] !== '') {
                    $this->page = $parts[0];
                }
                $parts = array_slice($parts, 1);
            }
        }

        $this->addPartsToGet($parts);
    }
}
