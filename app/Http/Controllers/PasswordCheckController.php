<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PasswordCheckController extends Controller
{
    /**
     * Check if the password is in the list of most common passwords.
     */
    public function check(Request $request)
    {
        // Ajouter cet en-tête pour forcer la réponse en JSON
        $request->headers->set('Accept', 'application/json');

        // Vérification si l'utilisateur a la fonctionnalité "checkpassword"
        if (!$request->user()->hasFunctionality('checkpassword')) {
            return response()->json(['error' => 'You do not have the functionality.'], 403);
        }

        $password = $request->input('password');

        // Chargement de la liste des mots de passe communs depuis le fichier
        $commonPasswords = Storage::disk('public')->get('10k-most-common.txt');
        $passwordsArray = explode("\n", $commonPasswords);

        // Vérification si le mot de passe est dans la liste
        if (in_array(trim($password), array_map('trim', $passwordsArray))) {
            return response()->json(['message' => 'Ce mot de passe est trop commun :('], 400);
        }

        return response()->json(['message' => 'Ce mot de passe est super secure !']);
    }

}
