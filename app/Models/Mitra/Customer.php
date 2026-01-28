<?php

namespace App\Models\Mitra;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'customer_name',
        'customer_alamat',
        'customer_email',
        'customer_phone1',
        'customer_phone2',
        'customer_picname1',
        'customer_picphone1',
        'customer_picname2',
        'customer_picphone2',
        'customer_harga',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function customerMarketing()
    {
        return $this->hasOne(CustomerMarketing::class, 'customer_id', 'id');
    }
}
