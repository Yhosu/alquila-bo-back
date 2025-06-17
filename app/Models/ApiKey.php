<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class ApiKey extends Authenticatable  {

    use HasApiTokens;
	protected $table   = 'api_keys';
    protected $casts   = ['active' => 'boolean', 'id' => 'string'];
    protected $hidden  = [ 'created_at', 'updated_at', 'last_used_at', 'id' ];
	public $timestamps = true;

    public function createToken(string $name, array $abilities = ['*']) {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(240)),
            'abilities' => $abilities,
        ]);
        return new NewAccessToken($token, $plainTextToken);
    }

    public function providers() {
        return $this->belongsToMany( Provider::class, 'api_key_providers','api_key_id','provider_id' );
    }
}