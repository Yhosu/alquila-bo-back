<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model {
    use HasFactory;
	protected $table  = 'products';
    protected $hidden = [ 'created_at', 'updated_at' ];
    protected $appends = ['provider_name', 'data_debt'];
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

    public function company() {
        return $this->hasOne( Company::class, 'id','company_id' );
    }

    public function product_characteristics() {
        return $this->hasMany( ProductCharacteristic::class, 'product_id','id' );
    }

    public function product_filters() {
        return $this->hasMany( ProductFilter::class, 'product_id','id' );
    }

    public function galleries() {
        return $this->hasMany( Gallery::class, 'relation_id','id' )->where( 'type', 'product')->where('enabled', 1);
    }
}