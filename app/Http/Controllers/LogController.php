<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;
use App\Models\User;

class LogController extends Controller
{
    /**
     * Récupère tous les logs
     */
    public function getAllLogs()
    {
        return Logs::orderBy('created_at', 'desc')->paginate(10); // Pagination des logs
    }

    /**
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
