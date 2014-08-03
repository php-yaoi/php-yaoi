<?php

abstract class Entity_Repository_MySQL implements Entity_Repository, Entity_Repository_DatabaseRequirements {
    public function add(Entity $entity)
    {
        $data = $entity->toArray();
        $db = static::getDatabase();
        $table = static::getTableName();
        $fields = '';
        $values = '';
        foreach ($data as $k => $v) {
            $fields .= "`$k`,";
            $values .= $db->quote($v) . ',';
        }
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);
        $db->query('INSERT INTO `' . $table . '` (' . $fields . ') VALUES (' . $values . ')');
        if (static::isAutoPrimaryKey()) {
            $primaryKey = static::getPrimaryKey();
            if (is_string($primaryKey)) {
                $entity->$primaryKey = $db->lastInsertId();
            }
        }
    }

    public function byPrimaryKey($key)
    {
        // TODO: Implement byPrimaryKey() method.
    }

    public function byEntity()
    {
        // TODO: Implement byEntity() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    /**
     * @return Entity[]
     */
    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    public static function deleteEntity(Entity $entity)
    {
        // TODO: Implement deleteEntity() method.
    }

} 