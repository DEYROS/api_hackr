<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;
use App\Models\User;

class LogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/logs",
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
     *     path="/logs/user",
     *     summary="Get logs for a specific user",
     *     tags={"Logs"},
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         required=true,
     *         description="User ID to filter logs",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User logs retrieved successfully"),
     *     @OA\Response(response=400, description="User ID is required"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Récupère les logs d'un utilisateur spécifique, en fonction du paramètre user
     */
    public function getUserLogs(Request $request)
    {
        $userId = $request->query('user'); // Récupérer le paramètre user

        if ($userId) {
            return Logs::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
        }

        return response()->json(['error' => 'User ID is required'], 400);
    }

    /**
     * @OA\Get(
     *     path="/logs/functionality",
     *     summary="Get logs for a specific functionality, optionally filtered by user",
     *     tags={"Logs"},
     *     @OA\Parameter(
     *         name="functionality",
     *         in="query",
     *         required=true,
     *         description="Functionality name to filter logs",
     *         @OA\Schema(type="string")
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
     * Récupère les logs d'une fonctionnalité et, éventuellement, d'un utilisateur
     */
    public function getFunctionalityLogs(Request $request)
    {
        $functionality = $request->query('functionality'); // Récupérer la fonctionnalité
        $userId = $request->query('user'); // Récupérer l'ID utilisateur si fourni

        $query = Logs::where('functionality', $functionality);

        // Ajouter un filtre par utilisateur si l'ID est fourni
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }
}
