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
     * Ajouter une fonctionnalité à un utilisateur.
     */
    public function addFunctionality(Request $request)
    {
        // Valider l'entrée
        $request->validate([
            'functionality' => 'required|string|exists:functionalities,name',
            'user' => 'required|integer|exists:users,id', // Valider que l'utilisateur cible existe
        ]);

        // Récupérer la fonctionnalité et l'utilisateur cible
        $functionality = Functionality::where('name', $request->input('functionality'))->first();
        $targetUser = User::find($request->input('user'));

        // Associer la fonctionnalité à l'utilisateur cible
        $targetUser->functionalities()->attach($functionality->id);

        // Enregistrer le log avec user_id (utilisateur authentifié) et target_id (utilisateur cible)
        Logs::create([
            'user_id' => auth()->id(),  // ID de l'utilisateur authentifié qui fait l'action
            'target_id' => $targetUser->id,  // ID de l'utilisateur cible
            'action' => 'Ajout de fonctionnalité à l\'utilisateur ' . $targetUser->name,
            'functionality' => $request->input('functionality')
        ]);

        return response()->json(['message' => 'Fonctionnalité ajoutée avec succès.']);
    }

    /**
     * Supprimer une fonctionnalité d'un utilisateur.
     */
    public function removeFunctionality(Request $request)
    {
        // Valider l'entrée
        $request->validate([
            'functionality' => 'required|string|exists:functionalities,name',
            'user' => 'required|integer|exists:users,id', // Valider que l'utilisateur cible existe
        ]);

        // Récupérer la fonctionnalité et l'utilisateur cible
        $functionality = Functionality::where('name', $request->input('functionality'))->first();
        $targetUser = User::find($request->input('user'));

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
            'functionality' => $request->input('functionality')
        ]);

        return response()->json(['message' => 'Fonctionnalité supprimée avec succès.']);
    }
}
