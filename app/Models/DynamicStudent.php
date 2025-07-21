<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicStudent extends Model
{
    protected $guarded = [];

    public static function setTableName($tableName)
    {
        $instance = new static;
        $instance->setTable($tableName);
        return $instance;
    }
}
