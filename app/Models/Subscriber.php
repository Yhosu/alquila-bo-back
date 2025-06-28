<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model {
    use HasFactory;
	protected $table    = 'subscriptions';
    protected $with     = [];
    protected $casts    = [ 'id' => 'string' ];
    protected $fillable = [
        'id',
        'email',
        'subscription_status',
        'confirmation_token',
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
