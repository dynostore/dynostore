<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilesInServer extends Model
{
    use HasFactory;

    protected $fillable = [
        "server_id",
        "keyfile",
    ];

    
}
