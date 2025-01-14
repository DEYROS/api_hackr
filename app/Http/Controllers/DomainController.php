<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Logs;
use Illuminate\Support\Facades\Log;

class DomainController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/crawl-domains",
     *     summary="Retrieve all domains and subdomains associated with a main domain",
     *     tags={"Func - Domain"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="domain", type="string", description="The main domain to search for subdomains", example="example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Domains retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=403, description="User does not have the necessary functionality"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crawlDomains(Request $request)
    {
        // Vérification si l'utilisateur a la fonctionnalité "crawler"
        if (!$request->user()->hasFunctionality('crawler')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Validation de l'entrée
        $validatedData = $request->validate([
            'domain' => 'required|string|regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        ]);

        $domain = $validatedData['domain'];

        try {
            // Appel à une API pour récupérer les domaines et sous-domaines (exemple : SecurityTrails)
            $response = Http::get('https://api.securitytrails.com/v1/domain/' . $domain . '/subdomains', [
                'apikey' => env('API_KEY_SECURITYTRAILS'),
            ]);

            // Vérification si la réponse est valide
            if ($response->failed()) {
                return response()->json(['error' => 'Erreur lors de la récupération des sous-domaines.'], 500);
            }

            // Extraire les résultats pertinents
            $data = $response->json();

            // Optionnel : Loguer l'action pour traçabilité
            Logs::create([
                'user_id' => auth()->id(),
                'target_id' => auth()->id(),
                'action' => auth()->user()->name . ' a recherché des sous-domaines pour ' . $domain,
                'functionality' => 'domain'
            ]);

            // Retourner les sous-domaines trouvés
            return response()->json([
                'message' => 'Sous-domaines récupérés avec succès.',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'appel à SecurityTrails: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne lors de la récupération des sous-domaines.'], 500);
        }
    }
}
