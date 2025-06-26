<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SocialNetworkCompany extends Model {
    use HasFactory;
	protected $table    = 'social_network_companies';
    protected $with     = [];
    protected $casts    = [ 'id' => 'string' ];
    protected $fillable = [
        'id',
        'name',
        'icon',
        'url'
    ];
	public $timestamps = false;

    public static function boot() {
        parent::boot();   
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }
}