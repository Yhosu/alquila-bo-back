<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Parameter extends Model {
    use HasFactory;
	protected $table  = 'parameters';
    protected $with   = [];
    protected $casts  = ['enabled' => 'boolean'];
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'description',
        'status',
    ];
	public $timestamps = true;
    const CREATED_AT = "date_of_creation";
	const UPDATED_AT = "last_modification";

    /*public function getKey()
    {
        return [$this->domain, $this->subdomain];
    }

    public function getRouteKey()
    {
        return $this->getKey();
    }

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('domain', $this->domain)
                     ->where('subdomain', $this->subdomain);
    }*/

}
