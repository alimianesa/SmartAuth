<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AliveMelliPayamakLog extends Model
{
    protected $fillable = [
        'author_id',
        'user_name',
        'password',
        'to',
        'from',
        'text',
        'is_flash',
        'response_status_code',
        'response_body',
    ];
}
