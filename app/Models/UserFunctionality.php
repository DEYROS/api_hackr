<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserFunctionality
 * 
 * @property int $id
 * @property int $user_id
 * @property int $functionality_id
 * 
 * @property Functionality $functionality
 * @property User $user
 *
 * @package App\Models
 */
class UserFunctionality extends Model
{
	protected $table = 'user_functionalities';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'functionality_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'functionality_id'
	];

	public function functionality()
	{
		return $this->belongsTo(Functionality::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
