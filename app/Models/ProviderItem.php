<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProviderItem extends Model {
    use HasFactory;
	protected $table = 'provider_items';
    protected $casts = ['id' => 'string'];
    protected $with = [];
    protected $fillable = [];
	public $timestamps = true;
    public $incrementing = false;

    public static function boot() {
        parent::boot();   
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }    

    public function account() {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    public function provider() {
        return $this->hasOne(Provider::class, 'id', 'provider_id');
    }
}
