<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function getAllLogs()
    {
        return Log::orderBy('created_at', 'desc')->paginate(10); // Pagination des logs
    }

    public function getUserLogs(User $user)
    {
        return Log::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getFunctionalityLogs($functionality)
    {
        return Log::where('functionality', $functionality)->orderBy('created_at', 'desc')->paginate(10);
    }
}
