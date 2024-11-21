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
     *     path="/verify-email",
     *     summary="Verify an email address",
     *     tags={"Func - Email Existence"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="The email address to verify",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="api_key",
     *         in="query",
     *         required=true,
     *         description="The API key for Hunter.io",
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
     *         description="Bad Request - Email and API key are required",
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
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Verify the existence of an email address by using an external API.
     */
    public function verifyEmail(Request $request)
    {
        Log::info("Vérification de l'email reçue");

        // Récupérer l'email et la clé API des paramètres de requête
        $email = $request->query('email');
        $apiKey = $request->query('api_key');

        // Vérifier que l'email et la clé API sont fournis
        if (!$email || !$apiKey) {
            return response()->json(['error' => 'Email et clé API sont requis.'], 400);
        }

        // Valider l'email
        $validatedData = $request->validate([
            'email' => 'required|email'
        ]);

        Log::info("Email validé : " . $email);

        // Appel à l'API Hunter.io pour vérifier l'email
        $url = 'https://api.hunter.io/v2/email-verifier';

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
            return response()->json([
                'error' => 'Erreur lors de la requête API : ' . $e->getMessage()
            ], 500);
        }
    }
}
