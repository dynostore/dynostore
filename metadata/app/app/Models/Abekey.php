<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abekey extends Model
{
    use HasFactory;

    protected $fillable = [
        "keyfile",
        "url"
    ];
}
