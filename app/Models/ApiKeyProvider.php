<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKeyProvider extends Model {
    use HasFactory;
	protected $table    = 'api_key_providers';
    protected $with     = [];
    protected $casts    = [ 'id' => 'string' ];
    protected $fillable = [
        'id',
        'api_key_id',
        'provider_id',
    ];
	public $timestamps = false;
}