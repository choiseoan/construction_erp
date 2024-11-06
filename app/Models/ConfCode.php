<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfCode extends Model
{
    use HasFactory;

    protected $table = 'conf_code';

    public $incrementing = false;
    protected $keyType = 'string';


}
