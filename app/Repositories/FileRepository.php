<?php

namespace App\Repositories;

use App\Models\File;
use App\Repositories\Contracts\FileRepositoryInterface;

class FileRepository implements FileRepositoryInterface
{
    public function getBlankModel()
    {
        return File::query();
    }

    public function create($data)
    {
        return $this->getBlankModel()->create($data);
    }

    public function find($id)
    {
        $query = $this->getBlankModel()->find($id);

        return $query;
    }
}
