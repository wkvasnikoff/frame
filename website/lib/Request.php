<?php

class Request
{
	public $page;

	public function __construct()
	{
		$this->parseUrl();
	}

	/**
	 * This method can be overridden if url should be parse differently
	 */
	protected function parseUrl()
	{
		$this->page = 'index';

		$requestNoParam = $_SERVER['REQUEST_URI'];
		$index = strpos($requestNoParam, '?');
		if($index !== false) {
			$requestNoParam = substr($requestNoParam, 0, $index);
		}

		$parts = explode('/', $requestNoParam);
		
		if(count($parts) > 2) {
			$this->page = $parts[1];
			$parts = array_slice($parts, 2);
		}

		$getParams = array();
		while(isset($parts[0])) {
			$param = array_shift($parts);

			if(isset($parts[0])) {
				$getParams[$param] = array_shift($parts);
			} else {
				if($param !== '') {
					$getParams[$param] = '';
				}
			}
		}

		# allow parameters to be override in order given
		foreach($_GET as $p => $v) {
			$getParams[$p] = $v;
		}
		$_GET = $getParams;
	}
}
