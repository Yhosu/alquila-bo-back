<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageFolder extends Model {

    use HasFactory;
	protected $table = 'image_folders';
	public $timestamps = true;
    protected $dates = ['deleted_at'];

    use \Illuminate\Database\Eloquent\SoftDeletes;

	/* Creating rules */
	public static $rules_create = array(
		'name'=>'required',
		'extension'=>'required'
	);

	/* Updating rules */
	public static $rules_edit = array(
		'name'=>'required',
		'extension'=>'required'
	);

    public function image_sizes() {
        return $this->hasMany(ImageSize::class, 'parent_id');
    }
}
