<?php

class Database
{
	private $connection;

	public function __construct($db)
	{
		$xml = simplexml_load_file('/var/www/test/etc/db.xml');
		$dbinfo = $xml->xpath("db/connection[@name='" . $db . "']");
		$dbinfo = $dbinfo[0];

		$this->connection = new mysqli(
			$dbinfo->host,
			$dbinfo->username,
			$dbinfo->password,
			$dbinfo->dbname
		);
	}

	public function query($sql, $params = array(), $select=true)
	{
		foreach($params as &$param) {
			$param = $this->connection->real_escape_string($param);
		}

		$sql = vsprintf($sql, $params);
		$result = $this->connection->query($sql);

		if(!$select) {
			return;
		}

		$rows = array();
		if(!$result) {
			return array();
		}

		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		
		return $rows;
	}
}

