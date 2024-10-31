<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;

class PasswordGeneratorController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/password/generate",
     *     summary="Generate a secure password",
     *     tags={"Password"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="length", type="integer", description="Length of the password to generate")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Generated password"),
     *     @OA\Response(response=403, description="User does not have the necessary functionality"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Generate a secure password.
     */
    public function generate(Request $request)
    {
        $request->headers->set('Accept', 'application/json');

        // Vérification si l'utilisateur a la fonctionnalité "secure_password_generator"
        if (!$request->user()->hasFunctionality('secure_password_generator')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Validation de la longueur du mot de passe
        $request->validate([
            'length' => 'required|integer|min:12|max:32', // Longueur du mot de passe entre 8 et 32 caractères
        ]);

        $length = $request->input('length');
        $password = $this->generateSecurePassword($length);

        Logs::create([
            'user_id' => auth()->id(),
            'target_id' => auth()->id(),
            'action' => auth()->user()->name.' vient de générer un mot de passe sécurisé de longueur '.$length,
            'functionality' => 'secure_password_generator'
        ]);

        return response()->json(['password' => $password]);
    }

    /**
     * Generate a secure password.
     *
     * @param int $length
     * @return string
     */
    private function generateSecurePassword($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+[]{}|;:,.<>?';
        $charactersLength = strlen($characters);
        $randomPassword = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomPassword;
    }
}
