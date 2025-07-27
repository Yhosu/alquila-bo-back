<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Comment extends Model {
    use HasFactory;
	protected $table  = 'comments';
    protected $hidden = [ 'created_at', 'updated_at' ];
    protected $with = ['user'];
    protected $casts  = [ 'id' => 'string' ];
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";
    protected $fillable = [
        'id',
        'user_id',
        'product_id',
        'text',
        'comment_date'
    ];
	public $timestamps = true;

    public static function boot() {
        parent::boot();
        static::creating(function ($model) {
            $model->id = \Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
