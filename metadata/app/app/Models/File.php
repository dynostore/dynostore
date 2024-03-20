<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
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
        "required_chunks",
        "owner"
    ];

    protected $primaryKey = 'keyfile'; 
    public $incrementing = false;

}
