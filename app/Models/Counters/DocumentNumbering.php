<?php

namespace App\Models\Counters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentNumbering extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'document_numbering_name',
        'document_numbering_prefix',
        'document_numbering_format',
        'document_numbering_numberlength',
        'document_numbering_currentnumber',
        'document_numbering_reset_type',
        'user_id',
    ];
}
