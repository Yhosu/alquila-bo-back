<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Transaction;

class Callback extends Model {
    use HasFactory;
	protected $table  = 'callbacks';
	public $timestamps = true;

}