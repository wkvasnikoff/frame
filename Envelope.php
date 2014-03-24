<?php
namespace frame;

class Envelope
{
    public $data;
    public $message;
    public $redirectUrl;
    public $reload;
    public $container;
    public $type = 'Notice';

    public function render()
    {
        echo json_encode($this);
        exit;
    }
}
