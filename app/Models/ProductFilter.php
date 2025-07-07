<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductFilter extends Model {
    use HasFactory;
	protected $table  = 'product_filters';
    protected $casts  = [ 'id' => 'string' ];
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";
    protected $fillable = [
        'id',
        'product_id',
        'company_filter_id',
        'description'
    ];
	public $timestamps = true;
    public $incrementing = false;

    public static function boot() {
        parent::boot();   
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }

    public function product() {
        return $this->hasOne(Product::class,'id','product_id');
    }

    public function product_filter_values() {
        return $this->hasMany(ProductFilterValue::class, 'company_filter_id', 'id');
    }
}