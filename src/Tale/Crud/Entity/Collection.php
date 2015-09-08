<?php

namespace Tale\Crud\Entity;

use Tale\Collection as TaleCollection;

class Collection extends TaleCollection {

    public function createAll()
    {

        foreach ($this as $entity)
            yield $entity->create();
    }

    public function loadAll()
    {

        foreach ($this as $entity)
            yield $entity->load();
    }

    public function saveAll()
    {

        foreach ($this as $entity)
            yield $entity->save();
    }

    public function removeAll()
    {

        foreach ($this as $entity)
            yield $entity->remove();
    }
}