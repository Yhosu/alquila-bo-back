<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model {
    use HasFactory;
	protected $table  = 'categories';
    protected $with   = [];
    protected $casts  = ['id' => 'string', 'active' => 'boolean'];
    protected $fillable = [
        'id',
        'name',
        'slug',
        'image',
        'active',
    ];
	public $timestamps = true;

    public function providers() {
        return $this->belongsToMany( Provider::class, 'category_provider','category_id','provider_id' );
    }
}