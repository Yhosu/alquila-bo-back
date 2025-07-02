<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationTemplate extends Model {
    use HasFactory;
	protected $table    = 'notification_templates';
    protected $with     = [];
    protected $casts    = [ 'cod_notification' => 'string' ];
    protected $fillable = [
        'cod_notification',
        'description',
        'type',
        'subject',
        'template',
    ];
	public $timestamps = true;
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";

    /*public static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }*/
}
