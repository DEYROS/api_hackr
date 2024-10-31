<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/logs",
     *     summary="Get all logs",
     *     tags={"Logs"},
     *     @OA\Response(response=200, description="Logs retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Récupère tous les logs
     */
    public function getAllLogs()
    {
        return Logs::orderBy('created_at', 'desc')->paginate(10); // Pagination des logs
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}/logs",
     *     summary="Get logs for a specific user",
     *     tags={"Logs"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="User ID to filter logs",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User logs retrieved successfully"),
     *     @OA\Response(response=400, description="User ID is required"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Récupère les logs d'un utilisateur spécifique via l'ID utilisateur dans l'URL
     */
    public function getUserLogs($userId)
    {
        return Logs::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * @OA\Get(
     *     path="/api/functionalities/{functionality}/logs",
     *     summary="Get logs for a specific functionality, optionally filtered by user",
     *     tags={"Logs"},
     *     @OA\Parameter(
     *         name="functionality",
     *         in="path",
     *         required=true,
     *         description="Functionality ID to filter logs",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=false,
     *         description="User ID to further filter logs",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Functionality logs retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Récupère les logs d'une fonctionnalité, filtrés éventuellement par l'utilisateur
     */
    public function getFunctionalityLogs($functionalityId, Request $request)
    {
        $userId = $request->query('user'); // Récupérer l'ID utilisateur si fourni

        $query = Logs::where('functionality', $functionalityId);

        // Ajouter un filtre par utilisateur si l'ID est fourni
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}
