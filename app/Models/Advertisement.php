<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Advertisement extends Model {
    use HasFactory;
	protected $table  = 'advertisements';
    protected $with   = [];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];
    protected $appends = ['image_url'];
    protected $fillable = [
        'id',
        'image',
        'name',
        'order',
        'description',
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

    public function getImageUrlAttribute() {
        return asset( \Asset::get_image_path( 'advertisement-image', 'original', $this->attributes['image'] ) );
    }
}
