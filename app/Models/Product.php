<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model {
    use HasFactory;
	protected $table  = 'products';
    protected $hidden = [ 'created_at', 'updated_at' ];
    protected $appends = ['image_url', 'category_name'];
    protected $with = ['product_characteristics', 'product_filters', 'galleries'];
    protected $casts  = [ 'id' => 'string' ];
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";
    protected $fillable = [
        'id',
        'company_id',
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'image',
        'top',
        'order'
    ];
	public $timestamps = true;
    public $incrementing = false;

    public static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }

    public function getImageUrlAttribute() {
        return asset( \Asset::get_image_path( 'product-image', 'normal', $this->attributes['image'] ) );
    }

    public function company() {
        return $this->hasOne( Company::class, 'id','company_id' );
    }

    public function product_characteristics() {
        return $this->hasMany( ProductCharacteristic::class, 'product_id','id' )->orderBy('order', 'ASC');
    }

    public function product_filters() {
        return $this->hasMany( ProductFilter::class, 'product_id','id' );
    }

    public function galleries() {
        return $this->hasMany( Gallery::class, 'entity_id','id' )->where( 'entity_type', 'product')->where('enabled', 1)->with('gallery_images');
    }

    public function getCategoryNameAttribute() {
        return $this->category->name ?? '';
    }

    public function category() {
        return $this->hasOne( Category::class, 'id', 'category_id');
    }
}
