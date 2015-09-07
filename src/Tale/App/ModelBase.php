<?php

namespace Tale\App;

use Tale\Data;

class ModelBase extends Data\Row
{

    public $id = 'id';


    public function getPrimaryKeyName($inflect = false)
    {

        return 'id';
    }
}