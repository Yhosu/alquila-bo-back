<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductFilterValue extends Model {
    use HasFactory;
	protected $table     = 'product_filter_values';
    public $incrementing = false;
    protected $casts     = [ 'id' => 'string', 'active' => 'boolean' ];
    protected $with      = [];
    protected $fillable  = [
        'id',
        'product_filter_id',
        'name',
        'configuration'
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