<?php

interface Entity_Repository {
    public function add(Entity $entity);

    public function byPrimaryKey($key);
    public function byEntity();
    public function delete();
    public function save();

    /**
     * @return Entity[]
     */
    public function getAll();
    public static function deleteEntity(Entity $entity);

}