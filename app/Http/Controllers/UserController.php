<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     @OA\Response(response=200, description="Users retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Internal server error"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Récupère tous les utilisateurs
     */
    public function getUsers()
    {
        $users = User::all();

        return response()->json($users);
    }
}
