<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gallery extends Model {
    use HasFactory;
	protected $table  = 'galleries';
    protected $with   = [];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];
    protected $fillable = [
        'id',
        'relation_id',
        'type',
        'image',
    ];
	public $timestamps = true;
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";

    public static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }
}