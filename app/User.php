<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table="tbl_user";
    protected $primaryKey="ID_USER";

    const UPDATED_AT="UPDATE_DATE";
    const CREATED_AT="INSERT_DATE";

    //protected $table="users";
    
    protected $fillable = [
        'USER_ID', 'USER_NAME', 'PASSWORD'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
