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
     *     security={{"bearerAuth": {}}},
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
     *     @OA\Response(response=400, description="Validation error or functionality not found"),
     *     @OA\Response(response=403, description="Unauthorized access")
     * )
     *
     * Ajouter une fonctionnalité à un utilisateur.
     */
    public function addFunctionality(Request $request, $userId)
    {
        // Forcer la réponse en JSON
        $request->headers->set('Accept', 'application/json');

        // Valider l'entrée
        $request->validate([
            'functionality' => 'required|string|exists:functionalities,name',
        ]);

        // Récupérer l'utilisateur cible
        $targetUser = User::findOrFail($userId);

        // Récupérer la fonctionnalité
        $functionality = Functionality::where('name', $request->input('functionality'))->first();

        // Associer la fonctionnalité à l'utilisateur cible
        $targetUser->functionalities()->attach($functionality->id);

        // Loguer l'ajout de fonctionnalité
        Logs::create([
            'user_id' => auth()->id(),  // Utilisateur authentifié qui effectue l'action
            'target_id' => $targetUser->id,  // Utilisateur cible
            'action' => 'Ajout de la fonctionnalité ' . $functionality->name . ' à l\'utilisateur ' . $targetUser->name,
            'functionality' => $functionality->id
        ]);

        // Réponse JSON
        return response()->json(['message' => 'Fonctionnalité ajoutée avec succès.'], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{user}/functionalities/{functionality}",
     *     summary="Remove functionality from a user",
     *     tags={"Functionality"},
     *     security={{"bearerAuth": {}}},
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
     *     @OA\Response(response=400, description="Validation error or functionality not associated with the user"),
     *     @OA\Response(response=403, description="Unauthorized access")
     * )
     *
     * Supprimer une fonctionnalité d'un utilisateur.
     */
    public function removeFunctionality(Request $request, $userId, $functionalityName)
    {
        // Forcer la réponse en JSON
        $request->headers->set('Accept', 'application/json');

        // Récupérer l'utilisateur cible
        $targetUser = User::findOrFail($userId);

        // Récupérer la fonctionnalité
        $functionality = Functionality::where('name', $functionalityName)->firstOrFail();

        // Vérifier si la fonctionnalité est associée à l'utilisateur
        $userFunctionality = UserFunctionality::where('user_id', $targetUser->id)
            ->where('functionality_id', $functionality->id)
            ->first();

        if (!$userFunctionality) {
            return response()->json(['error' => 'Cette fonctionnalité n\'est pas associée à l\'utilisateur.'], 400);
        }

        // Supprimer la fonctionnalité de l'utilisateur
        $userFunctionality->delete();

        // Loguer la suppression de fonctionnalité
        Logs::create([
            'user_id' => auth()->id(),  // Utilisateur authentifié qui effectue l'action
            'target_id' => $targetUser->id,  // Utilisateur cible
            'action' => 'Suppression de la fonctionnalité ' . $functionality->name . ' de l\'utilisateur ' . $targetUser->name,
            'functionality' => $functionality->id
        ]);

        // Réponse JSON
        return response()->json(['message' => 'Fonctionnalité supprimée avec succès.'], 200);
    }
}
