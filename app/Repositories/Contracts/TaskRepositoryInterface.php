<?php
namespace App\Repositories\Contracts;

use App\Repositories\Contracts\BaseRepositoryInterface;

interface TaskRepositoryInterface extends BaseRepositoryInterface
{
    public function filteredList(array $filters);
}