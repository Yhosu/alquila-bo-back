<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Faq extends Model {
    use HasFactory;
	protected $table  = 'faqs';
    protected $with   = [];
    protected $appends = ['image_url'];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];
    protected $fillable = [
        'id',
        'name',
        'description',
        'image',
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
        return asset( \Asset::get_image_path( 'faq-image', 'normal', $this->attributes['image'] ) );
    }
}