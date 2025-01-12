<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Functionality
 * 
 * @property int $id
 * @property string $name
 * 
 * @property Collection|UserFunctionality[] $user_functionalities
 *
 * @package App\Models
 */
class Functionality extends Model
{
	protected $table = 'functionalities';
	public $timestamps = false;

	protected $fillable = [
		'name'
	];

	public function user_functionalities()
	{
		return $this->hasMany(UserFunctionality::class);
	}
}
