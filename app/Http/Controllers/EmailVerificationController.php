<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Logs;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/verify-email",
     *     summary="Verify an email address",
     *     tags={"Func - Email Existence"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="The email address to verify",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verification result",
     *         @OA\JsonContent(
     *             @OA\Property(property="email_exist", type="boolean", description="Whether the email exists or not"),
     *             @OA\Property(property="result", type="object", description="Detailed verification result")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Email is required",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User does not have the necessary functionality",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     *
     * Verify the existence of an email address by using an external API.
     */
    public function verifyEmail(Request $request)
    {
        // Vérification si l'utilisateur a la fonctionnalité "verif_email"
        if (!$request->user()->hasFunctionality('verif_email')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Récupérer l'email des paramètres de requête
        $email = $request->query('email');

        // Vérifier que l'email est fourni
        if (!$email) {
            return response()->json(['error' => 'Email est requis.'], 400);
        }

        // Validation de l'email
        $validatedData = $request->validate([
            'email' => 'required|email'
        ]);

        Log::info("Email validé : " . $email);

        // Appel à l'API Hunter.io pour vérifier l'email
        $url = 'https://api.hunter.io/v2/email-verifier';
        $apiKey = env('API_KEY_HUNTER_IO'); // Utilisation de la clé API dans le .env

        try {
            // Appel à l'API avec l'email et la clé API
            $response = Http::get($url, [
                'email' => $email,
                'api_key' => $apiKey,
            ]);

            // Vérification de la réponse de l'API
            if ($response->successful()) {
                $verificationData = $response->json();

                // Extraire les données importantes pour la réponse
                $status = $verificationData['data']['status'] ?? 'Unknown';
                $result = $verificationData['data'] ?? [];

                // Déterminer si l'email existe en fonction du statut
                $emailExist = ($status === 'valid' || $status === 'deliverable');

                // Loguer l'action de vérification
                Logs::create([
                    'user_id' => auth()->id(),
                    'target_id' => auth()->id(),
                    'action' => auth()->user()->name . ' a vérifié l\'email ' . $email,
                    'functionality' => 'verif_email'
                ]);

                // Retourner la réponse avec les informations
                return response()->json([
                    'email_exist' => $emailExist,
                    'result' => $result
                ], 200);
            }

            // Si l'API retourne une erreur
            return response()->json([
                'error' => 'Erreur lors de la vérification de l\'email',
                'message' => $response->body()
            ], 500);
        } catch (\Exception $e) {
            // En cas d'erreur lors de l'appel API
            Log::error('Erreur lors de la vérification de l\'email: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erreur lors de la requête API : ' . $e->getMessage()
            ], 500);
        }
    }
}
