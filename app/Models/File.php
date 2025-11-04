<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $fillable = ['name', 'path', 'mime_type', 'size', 'created_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Utility Functions
    protected $appends = ['path_asset'];

    public function getPathAssetAttribute()
    {
        return asset($this['path']);
    }
}
