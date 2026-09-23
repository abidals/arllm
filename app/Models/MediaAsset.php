<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['user_id', 'path', 'original_name', 'mime_type', 'size'];
}