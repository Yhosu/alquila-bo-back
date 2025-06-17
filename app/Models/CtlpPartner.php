<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CtlpPartner extends Model {
    use HasFactory;
	protected $table  = 'ctlp_partners';
    protected $casts  = ['id' => 'string', 'active' => 'boolean'];
    protected $fillable = [
        'id',
        'name',
        'ci_number',
        'code',
        'active'
    ];
	public $timestamps = true;
}