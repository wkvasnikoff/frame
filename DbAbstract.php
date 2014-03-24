<?php

namespace frame;

abstract class DbAbstract
{
    protected static $connection;
    protected static $tableName;
    protected static $keys;

    public function __construct()
    {
    }

    public function __get($name)
    {
        $name = "m_$name";
        if (isset($this->$name)) {
            return $this->$name;
        }
        return;
    }

    public function __set($name, $value)
    {
        $name = "m_$name";
        if (property_exists($this, "$name")) {
            $this->$name = $value;
        } else {
            echo "property $name does not exist on class";
        }
    }

    public static function getByKey($keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $sql = sprintf('select * from %s where ', static::$tableName);
        foreach (static::$keys as $key) {
            $sql .= " $key = '%s'";
        }

        $objs = self::getByQuery($sql, $keys);
        if (count($objs) === 1) {
            return $objs[0];
        }

        return false;
    }

    public static function getByQuery($sql, array $params = [])
    {
        $db = new Database(static::$connection);
        $rows = $db->query($sql, $params);

        $objs = [];

        foreach ($rows as $row) {
            $class = get_called_class();
            $obj = new $class();
            foreach ($row as $key => $value) {
                $obj->$key = $value;
            }
            $objs[] = $obj;
        }

        return $objs;
    }

    public function save($manualInsert = false, $fetchId = false)
    {
        $db = new Database(static::$connection);
        $fields = [];
        $values = [];

        # get relevant properties
        $reflect = new \ReflectionClass($this);
        $props = $reflect->getProperties(\ReflectionProperty::IS_PROTECTED);

        $nameValue = [];
        foreach ($props as $prop) {
            $name = $prop->name;
            if (preg_match('/^m_/', $name)) {
                $shortName = substr($name, 2);
                if (in_array($shortName, static::$keys && !$manualInsert)) {
                    continue;
                }
                $fields[] = $shortName;
                $values[] = $this->$shortName;
            }
        }

        # figure out if insert or update
        $op = $manualInsert ? 'insert' : 'update';

        foreach (static::$keys as $key) {
            if (!$this->$key) {
                $op = 'insert';
                break;
            }
        }

        if ($op === 'insert') {
            $valueFieldPercents = [];
            foreach ($fields as $field) {
                $valueFieldPercents[] = '\'%s\'';
            }
            $valueFieldPercents = join(',', $valueFieldPercents);
            $fields = join(', ', $fields);
            $sql = sprintf('insert into %s (%s) values (%s)', static::$tableName, $fields, $valueFieldPercents);

            $db->query($sql, $values, false);

            if ($fetchId && !$manualInsert) {
                $id = $db->insertId();
                $key = static::$keys[0];
                $this->$key = $id;
            }
        } else {
            # Set Clause
            $setClause = [];
            foreach ($fields as $field) {
                $setClause[] = "$field='%s'";
            }
            $setClause = join(',', $setClause);

            # Where Clause
            $keyFields = [];
            $keyValues = [];
            foreach ($props as $prop) {
                $name = $prop->name;
                if (preg_match('/^m_/', $name)) {
                    $shortName = substr($name, 2);

                    if (!in_array($shortName, static::$keys)) {
                        continue;
                    }

                    $kFields[] = "$shortName='%s'";
                    $kValues[] = $this->$shortName;
                }
            }
            $whereClause = join(',', $kFields);

            $sql = sprintf('update %s set %s where %s', static::$tableName, $setClause, $whereClause);
            $values = array_merge($values, $kValues);
            $db->query($sql, $values, false);
        }
    }
}
