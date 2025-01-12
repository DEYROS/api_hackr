<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Log
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int|null $target_id
 * @property string $action
 * @property string|null $functionality
 * @property Carbon $created_at
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Logs extends Model
{
	protected $table = 'logs';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'target_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'target_id',
		'action',
		'functionality'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
