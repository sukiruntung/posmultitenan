<?php

namespace App\Models\Mitra;

use App\Models\Accesses\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerMarketing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'marketing_id',
        'user_id',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function marketing()
    {
        return $this->belongsTo(Marketing::class, 'marketing_id');
    }
}
