<?php

namespace App\Models;


class TaskDependency extends BaseModel
{

    protected $fillable = ['task_id', 'depends_on_task_id'];
}