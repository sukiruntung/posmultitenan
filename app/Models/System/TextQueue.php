<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TextQueue extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'text',
        'priority',
    ];
}
