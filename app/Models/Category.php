<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model {
    use HasFactory;
	protected $table  = 'categories';
    protected $with   = [];
    protected $casts  = ['id' => 'string'];
    protected $appends = ['image_url'];
    protected $fillable = [
        'id',
        'name',
        'description',
        'slug',
        'image',
        'icon',
        'enabled',
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

    public function products() {
        return $this->hasMany( Product::class, 'category_id', 'id');
    }

    public function getImageUrlAttribute() {
        return asset( \Asset::get_image_path( 'category-image', 'normal', $this->attributes['image'] ) );
    }
}