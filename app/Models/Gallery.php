<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Gallery extends Model {
    use HasFactory;
	protected $table  = 'galleries';
    protected $with   = [];
    protected $casts  = ['id' => 'string', 'enabled' => 'boolean'];
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'entity_id',
        'entity_type',
        'subtype',
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

    public function gallery_images() {
        return $this->hasMany( GalleryImage::class, 'gallery_id','id' )->where('enabled', 1);
    }

    public function company(){
        return $this->belongsTo(Company::class, 'entity_id');
    }
}
