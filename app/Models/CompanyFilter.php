<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyFilter extends Model {
    use HasFactory;
	protected $table    = 'company_filters';
    protected $hidden   = [ 'created_at', 'updated_at', 'deleted_at', 'active', 'api_key_id' ];
    protected $casts    = ['id' => 'string'];
    protected $fillable = [
        'api_key_id',
        'user_id',
        'tenant_id',
        'tenant_url',
        'customer_id',
        'name',
        'email',
        'cellphone',
        'nit',
        'ci_number',
        'active'
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