<?php
namespace frame;

abstract class AsyncController extends Controller
{
    public $envelope;

    public function __construct()
    {
        parent::__construct();
        $this->envelope = new Envelope();
    }

    public function renderEnvelope()
    {
        header('Content-Type: application/json');
        $this->envelope->render();
    }

    /**
     * array(
        'rules' => array(

        ),
        messages => array(

        )
     );
     */
    public function checkForm($map)
    {
        #TODO Add validation

    }

    protected function dbDateFormat($date)
    {
        $timestamp = strtotime($date);
        return date('Y-m-d H:i:s', $timestamp);
    }

    public function getParam($key, $default = null, $postOnly = false)
    {
        if ($postOnly) {
            if (array_key_exists($key, $_POST)) {
                return $_POST[$key];
            } else {
                return $default;
            }

        } else {
            if (array_key_exists($key, $_REQUEST)) {
                return $_REQUEST[$key];
            } else {
                return $default;
            }
        }
    }
}
