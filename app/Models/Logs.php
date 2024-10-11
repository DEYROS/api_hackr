<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    public $timestamps = false; // Pas de gestion d'updated_at
    protected $fillable = ['user_id', 'target_id', 'action', 'functionality', 'created_at'];
}
