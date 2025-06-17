<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Account extends Model {
    use HasFactory;
	protected $table  = 'accounts';
    protected $hidden = [ 'created_at', 'updated_at' ];
    protected $appends = ['provider_name', 'data_debt'];
    protected $casts  = [ 'id' => 'string' ];
    protected $fillable = [
        'id',
        'profile_id',
        'provider_id',
        'internal_code',
        'metadata',
        'name',
        'currency',
        'external_profile_id',
        'tenant_url',
        'tenant_id'
    ];
	public $timestamps = true;
    public $incrementing = false;

    public static function boot() {
        parent::boot();   
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }

    public function profile() {
        return $this->hasOne(Profile::class, 'id', 'profile_id');
    }

    public function provider() {
        return $this->hasOne(Provider::class, 'id', 'provider_id');
    }

    public function getProviderNameAttribute() {
        $name = $this->provider->name ?? '';
        return ucwords($name);
    }

    public function getDataDebtAttribute() {
        return \Func::getMetadataAccount( $this->attributes['metadata'] ?? '{}', $this->provider->code ?? '');
    }
}