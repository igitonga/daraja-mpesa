<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaAuthToken extends Model
{
    use HasFactory;

    protected $table = 'mpesa_auth_token';

    protected $primaryKey = 'id';

    protected $fillable = ['token','expires_at'];
    
}
