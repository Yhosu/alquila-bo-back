<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductCharacteristic extends Model {
    use HasFactory;
	protected $table  = 'product_characteristics';
    protected $hidden = [ 'created_at', 'updated_at' ];
    protected $appends = ['provider_name', 'data_debt'];
    protected $casts  = [ 'id' => 'string' ];
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";
    protected $fillable = [
        'id',
        'name',
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
}