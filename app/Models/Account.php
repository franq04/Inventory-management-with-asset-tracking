<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    protected $table = 'accounts';
    protected $primaryKey = 'account_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'account_id', 'account_id');
    }
}
