<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Provider extends Model {
    use HasFactory;
	protected $table     = 'providers';
    public $incrementing = false;
    protected $casts     = [ 'id' => 'string', 'active' => 'boolean' ];
    protected $with      = [];
    protected $fillable  = [
        'id',
        'name',
        'slug',
        'code',
        'description',
        'class',
        'url_testing',
        'url_production',
        'image',
        'active'
    ];
	public $timestamps = true;
    protected $hidden = [ 'pivot', 'created_at', 'updated_at', 'external_payment', 'url_testing', 'url_production', 'class', 'code' ];

    public function provider_items() {
        return $this->hasMany(ProviderItem::class, 'provider_id', 'id');
    }
}