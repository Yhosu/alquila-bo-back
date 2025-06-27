<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageSize extends Model {
	
	protected $table = 'image_sizes';
	public $timestamps = false;

	/* Creating rules */
	public static $rules_create = array(
		'code'=>'required',
		'type'=>'required',
		'width'=>'integer',
		'height'=>'integer',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'code'=>'required',
		'type'=>'required',
		'width'=>'integer',
		'height'=>'integer',
	);

    public function parent() {
        return $this->belongsTo(ImageFolder::class);
    }
}