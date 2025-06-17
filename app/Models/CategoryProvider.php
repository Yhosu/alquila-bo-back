<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryProvider extends Model {
    use HasFactory;
	protected $table  = 'category_provider';
    protected $with = [];
    protected $casts = ['id'=>'string'];
    protected $fillable = [
        'id',
        'category_id',
        'provider_id'
    ];
	public $timestamps = true;
}