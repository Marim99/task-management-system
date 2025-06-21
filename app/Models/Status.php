<?php

namespace App\Models;

class Status extends BaseModel
{
    protected $fillable = ['name', 'label'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
