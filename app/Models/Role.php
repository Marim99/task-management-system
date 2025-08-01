<?php

namespace App\Models;

class Role extends BaseModel
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
