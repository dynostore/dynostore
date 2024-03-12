<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chunk extends Model
{
    use HasFactory;
    protected $fillable = [
        "keyfile",
        "name",
        "size",
        "server_id"
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public static function booted() {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
