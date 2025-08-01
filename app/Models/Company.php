<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model {
    use HasFactory;
	protected $table  = 'companies';
    protected $with   = ['social_network_company'];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];
    protected $fillable = [
        'id',
        'sector_id',
        'name',
        'description',
        'slug',
        'order',
        'image',
        'cellphone',
        'address',
        'website'
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
    public function social_network_company() {
        return $this->hasMany( SocialNetworkCompany::class, 'company_id', 'id');
    }
}