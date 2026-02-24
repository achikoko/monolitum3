<?php

namespace monolitum\model;

/**
 * Interface for Nodes to find a parent that can d CRUD over entities.
 */
interface EntityPersister
{

    public function _notifyEntityChanged(Entity $entity): void;

    /**
     * @param Entity $entity
     * @return int[]
     */
    public function _executeInsertEntity(Entity $entity): array;

    /**
     * @param Entity $entity
     * @return int[]
     */
    public function _executeUpdateEntity(Entity $entity): array;

    /**
     * @param Entity $entity
     * @return int[]
     */
    public function _executeDeleteEntity(Entity $entity): int;

}
