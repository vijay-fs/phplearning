<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pics extends Model
{
     protected $connection = 'mongodb';
     protected $fillable = ['filename', 'image'];
}
