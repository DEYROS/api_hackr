<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Functionality;
use App\Models\UserFunctionality;

class FunctionalityController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/users/{user}/functionalities",
     *     summary="Add functionality to a user",
     *     tags={"Functionality"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="The ID of the user to add functionality to",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="functionality", type="string", description="The name of the functionality to add")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Functionality added successfully"),
     *     @OA\Response(response=400, description="Validation error or functionality not found")
     * )
     *
     * Ajouter une fonctionnalité à un utilisateur.
     */
    public function addFunctionality(Request $request, $userId)
    {
        // Valider l'entrée
        $request->validate([
            'functionality' => 'required|string|exists:functionalities,name',
        ]);

        // Récupérer l'utilisateur cible
        $targetUser = User::findOrFail($userId); // Utiliser findOrFail pour lever une exception si non trouvé

        // Récupérer la fonctionnalité
        $functionality = Functionality::where('name', $request->input('functionality'))->first();

        // Associer la fonctionnalité à l'utilisateur cible
        $targetUser->functionalities()->attach($functionality->id);

        // Enregistrer le log avec user_id (utilisateur authentifié) et target_id (utilisateur cible)
        Logs::create([
            'user_id' => auth()->id(),  // ID de l'utilisateur authentifié qui fait l'action
            'target_id' => $targetUser->id,  // ID de l'utilisateur cible
            'action' => 'Ajout de fonctionnalité à l\'utilisateur ' . $targetUser->name,
            'functionality' => $functionality->id
        ]);

        return response()->json(['message' => 'Fonctionnalité ajoutée avec succès.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}/functionalities/{functionality}",
     *     summary="Remove functionality from a user",
     *     tags={"Functionality"},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="The ID of the user to remove functionality from",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="functionality",
     *         in="path",
     *         required=true,
     *         description="The name of the functionality to remove",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Functionality removed successfully"),
     *     @OA\Response(response=400, description="Validation error or functionality not associated with the user")
     * )
     *
     * Supprimer une fonctionnalité d'un utilisateur.
     */
    public function removeFunctionality(Request $request, $userId, $functionalityName)
    {
        // Valider que l'utilisateur existe
        $targetUser = User::findOrFail($userId);

        // Récupérer la fonctionnalité
        $functionality = Functionality::where('name', $functionalityName)->firstOrFail();

        // Trouver l'enregistrement dans la table pivot pour l'utilisateur cible
        $userFunctionality = UserFunctionality::where('user_id', $targetUser->id)
            ->where('functionality_id', $functionality->id)
            ->first();

        // Vérifier si l'enregistrement existe
        if (!$userFunctionality) {
            return response()->json(['error' => 'Cette fonctionnalité n\'est pas associée à l\'utilisateur.'], 400);
        }

        // Supprimer l'enregistrement de la table pivot
        $userFunctionality->delete();

        // Enregistrer le log avec user_id (utilisateur authentifié) et target_id (utilisateur cible)
        Logs::create([
            'user_id' => auth()->id(),  // ID de l'utilisateur authentifié qui fait l'action
            'target_id' => $targetUser->id,  // ID de l'utilisateur cible
            'action' => 'Suppression de fonctionnalité de l\'utilisateur ' . $targetUser->name,
            'functionality' => $functionality->id
        ]);

        return response()->json(['message' => 'Fonctionnalité supprimée avec succès.']);
    }
}
