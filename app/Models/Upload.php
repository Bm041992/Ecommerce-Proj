<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'error_message',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
