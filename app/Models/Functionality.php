<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Functionality
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|UserFunctionality[] $user_functionalities
 *
 * @package App\Models
 */
class Functionality extends Model
{
	protected $table = 'functionalities';
	protected $primaryKey = 'id';
	public $incrementing = true;

	protected $fillable = [
		'name'
	];

	public function user_functionalities()
	{
		return $this->hasMany(UserFunctionality::class);
	}
}
