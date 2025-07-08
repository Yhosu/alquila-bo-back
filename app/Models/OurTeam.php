<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
 use Laravel\Sanctum\HasApiTokens;

class OurTeam extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
	protected $table  = 'our_team';
    protected $with   = [];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];  

    protected $fillable = [
        'name',
        'role',
        'bio',
        'photo_url',
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
