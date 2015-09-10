<?php

namespace Tale\Crud;

interface EntityInterface
{

    public function exists();

    public function create();
    public function load();
    public function save();
    public function remove();
}