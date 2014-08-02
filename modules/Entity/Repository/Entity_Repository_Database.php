<?php

abstract class Entity_Repository_Database implements Entity_Repository, Entity_Repository_DatabaseRequirements {
    public function add(Entity $entity)
    {
        $db = static::getDatabase();
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