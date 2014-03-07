<?php
namespace frame;

class Layout
{
    private $layout;
    private $containerData = [];

    public function __construct($layout)
    {
        $this->layout = $layout;
    }

    public function setContainerOutput($container, $output)
    {
        if (isset($this->containerData[$container])) {
            $this->containerData[$container] .= $output;
        } else {
            $this->containerData[$container] = $output;
        }
    }

    public function __get($name)
    {
        if (isset($this->containerData[$name])) {
            return $this->containerData[$name];
        }

        return;
    }

    public function render()
    {
        ob_start();
        include "../layouts/{$this->layout}.phtml";
        echo ob_get_clean();
    }
}
