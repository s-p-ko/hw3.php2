<?php
namespace App;

/**
 * Class Model
 * @package App
 */
abstract class Model
{
    protected const TABLE = '';
    public $id;

    /**
     * Get arrays of objects of a given class
     * @return array
     * @throws Exceptions\DbException
     */
    public static function findAll() : array
    {
        $db = new Db();
        $sql = 'SELECT * FROM ' . static::TABLE;
        return $db->query($sql, [], static::class);
    }

    /**
     * Find one object or return false
     * @param $id
     * @return mixed
     * @throws Exceptions\DbException
     */
    public static function findById($id)
    {
        $db = new Db();
        $sql = 'SELECT * FROM ' . static::TABLE . ' WHERE id = :id';
        $params = [':id' => $id];
        $res = $db->query($sql, $params, static::class);
        return $res ? $res[0] : false;
    }

    /**
     * Find all last objects, return array or false
     * @param int $limit
     * @return mixed
     * @throws Exceptions\DbException
     */
    public static function findAllLast(int $limit = 3)
    {
        $db = new Db();
        $sql = 'SELECT * FROM ' . static::TABLE . ' ORDER BY id DESC LIMIT ' . $limit;
        $res = $db->query($sql, [], static::class);
        return $res ? : false;
    }

    /**
     * Inserts data into table
     * @throws Exceptions\DbException
     */
    public function insert()
    {
        $db = new Db();
        $props = get_object_vars($this);
        $fields = [];
        $binds = [];
        $data = [];
        foreach ($props as $name => $value) {
            if ('id' == $name) {
                continue;
            }
            $fields[] = $name;
            $binds[] = ':' . $name;
            $data[':' . $name] = $value;
        }
        $sql = '
            INSERT INTO ' . static::TABLE .
            ' (' . implode(', ', $fields) . ')
            VALUES
             (' . implode(', ', $binds) . ')
             ';
        $db->execute($sql, $data);
        $this->id = $db->lastInsertId();
    }

    /**
     * Updates data in row of table
     * @throws Exceptions\DbException
     */
    public function update()
    {
        $db = new Db();
        $props = get_object_vars($this);
        $cols = [];
        $data = [];
        foreach ($props as $name => $value) {
            $data[':' . $name] = $value;
            if ('id' == $name) {
                continue;
            }
            $cols[] = $name .  ' = :' . $name;
        }
        $sql = '
            UPDATE ' . static::TABLE . ' 
            SET ' . implode(', ', $cols) . ' 
            WHERE id = :id
            ';
        $db->execute($sql, $data);
    }

    /**
     * Selects which this class's method to apply: insert() or update()
     * @throws Exceptions\DbException
     */
    public function save()
    {
        if (isset($this->id)) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    /**
     * Deletes chosen object by its id
     * @throws Exceptions\DbException
     */
    public function delete()
    {
        $db = new Db();
        $sql = 'DELETE FROM ' . static::TABLE . ' WHERE id = :id';
        $data = [':id' => $this->id];
        $db->execute($sql, $data);
    }
}
