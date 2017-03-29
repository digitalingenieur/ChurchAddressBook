<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
 
	protected $table = 'person';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'ct_id'
    ];

    public function partner(){
    	return $this->hasOne('App\Person','partner_id');
    }

    public function children(){
    	return $this->belongsToMany('App\Person','person_person','person_id','children_id');	
    }
}
