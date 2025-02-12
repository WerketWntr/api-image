<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    const TYPE_RESIZE = 'resize';

    const UPDATED_AT = null;

    protected $guarded = [];
}
