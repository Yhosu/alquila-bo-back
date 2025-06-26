<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompanyFilterValue extends Model {
    use HasFactory;
	protected $table     = 'company_filter_values';
    public $incrementing = false;
    protected $casts     = [ 'id' => 'string', 'active' => 'boolean' ];
    protected $with      = [];
    protected $fillable  = [
        'id',
        'company_filter_id',
        'name',
        'configuration'
    ];
	public $timestamps = true;
    protected $hidden = [ 'pivot', 'created_at', 'updated_at', 'external_payment', 'url_testing', 'url_production', 'class', 'code' ];

    public function provider_items() {
        return $this->hasMany(ProviderItem::class, 'provider_id', 'id');
    }
}