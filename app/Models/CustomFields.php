<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFields extends Model
{
    use HasFactory;

    protected $fillable = ['id','field_name', 'field_type'];
}
