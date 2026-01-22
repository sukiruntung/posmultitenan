<?php

namespace App\Models\Mitra;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'supplier_name',
        'supplier_alamat',
        'supplier_email',
        'supplier_phone1',
        'supplier_phone2',
        'supplier_picname1',
        'supplier_picphone1',
        'supplier_picname2',
        'supplier_picphone2',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
