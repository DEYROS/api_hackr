<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Functionality;
use App\Models\UserFunctionality;
use Illuminate\Http\Request;

class FunctionalityController extends Controller
{
    /**
     * Ajouter une fonctionnalité à un utilisateur.
     */
    public function addFunctionality(Request $request, User $user)
    {
        // Récupérer la fonctionnalité à partir du nom fourni
        $request->validate([
            'functionality' => 'required|string|exists:functionalities,name',
        ]);
        error_log( $request );
        $functionality = Functionality::where('name', $request->input('functionality'))->first();

        // Associer la fonctionnalité à l'utilisateur
        $user->functionalities()->attach($functionality->id);

        return response()->json(['message' => 'Fonctionnalité ajoutée avec succès.']);
    }

    /**
     * Supprimer une fonctionnalité d'un utilisateur.
     */
    public function removeFunctionality(Request $request, User $user)
    {
        // Trouver la fonctionnalité via son nom
        $functionality = Functionality::where('name', $request->input('functionality'))->first();
        error_log( $user->id );
        error_log( print_r($functionality, true) );
        // Trouver l'enregistrement dans la table pivot
        $userFunctionality = UserFunctionality::where('user_id', $user->id)
            ->where('functionality_id', $functionality->id)
            ->first();

        // Vérifier si l'enregistrement existe
        if (!$userFunctionality) {
            return response()->json(['error' => 'Cette fonctionnalité n\'est pas associée à l\'utilisateur.'], 400);
        }

        // Supprimer l'enregistrement de la table pivot
        $userFunctionality->delete();

        return response()->json(['message' => 'Fonctionnalité supprimée avec succès.']);
    }


}
