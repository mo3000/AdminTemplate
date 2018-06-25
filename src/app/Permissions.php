<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Permissions extends Model {
	protected $table = 'permissions';
	public $timestamps = false;

	protected $fillable = [
		'name', 'display_name', 'gymid',
		'path_name', 'project_name',
		'needed'
	];
}