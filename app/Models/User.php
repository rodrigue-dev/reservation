<?php

namespace App\Models;


class User extends Account
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'address',
    ];

    public function account() {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
    public function userProfile() {
        return $this->belongsTo(UserProfile::class, null, 'id');
    }
}
