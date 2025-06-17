<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProviderParameter extends Model {
    use HasFactory;
	protected $table  = 'provider_parameters';
    protected $with = [];
    protected $fillable = [];
	public $timestamps = true;
}