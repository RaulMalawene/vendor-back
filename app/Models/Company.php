<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'contact_name',
        'phone',
        'email',
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
    ];

    protected $hidden = ['bank_account_number'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}