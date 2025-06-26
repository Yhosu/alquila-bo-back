<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sector extends Model {
    use HasFactory;
	protected $table    = 'sectors';
    protected $with     = [];
    protected $casts    = [ 'id' => 'string' ];
    protected $fillable = [
        'id',
        'name',
        'description',
        'slug'
    ];
	public $timestamps = false;

    public static function boot() {
        parent::boot();   
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }
}