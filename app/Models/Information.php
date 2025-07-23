<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Information extends Model {
    use HasFactory;
	protected $table  = 'add_information';
    protected $with   = ['social_network'];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];
    protected $fillable = [
        'id',
        'name',
        'address',
        'phone_number',
        'email',
        'opening_hour',
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
    public function social_network() {
        return $this->hasMany( SocialNetwork::class, 'information_id', 'id');
    }
}