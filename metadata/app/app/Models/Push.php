<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    use HasFactory;

    protected $fillable = [
        "keyfile",
        "name",
        "size",
        "chunks",
        "is_encrypted",
        "hash",
        "disperse",
    ];
}
