<?php

namespace frame;

class Database
{
    private $connection;

    public function __construct($db)
    {
        $path = __DIR__ . '/../etc/db.xml';
        $xml = simplexml_load_file($path);
        $dbinfo = $xml->xpath("connection[@name='" . $db . "']");

        if (!$dbinfo) {
            echo "unable to find database: '$db'\n";
            exit;
        }
        $dbinfo = $dbinfo[0];

        $this->connection = new \mysqli(
            $dbinfo->host,
            $dbinfo->username,
            $dbinfo->password,
            $dbinfo->dbname
        );
    }

    public function query($sql, $params = [], $select = true)
    {
        foreach ($params as &$param) {
            $param = $this->connection->real_escape_string($param);
        }

        $sql = vsprintf($sql, $params);
        $result = $this->connection->query($sql);

        if (!$select) {
            return;
        }

        $rows = [];
        if (!$result) {
            return [];
        }

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }
}
