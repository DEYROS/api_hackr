<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Logs;
use Illuminate\Support\Facades\Log;

class CrawlerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/crawl-person",
     *     summary="Retrieve information about a person using their name, surname or pseudonym",
     *     tags={"Func - Crawler"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="The name, surname, or pseudonym of the person to search"),
     *         )
     *     ),
     *     @OA\Response(response=200, description="Information retrieved successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=403, description="User does not have the necessary functionality"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crawlPerson(Request $request)
    {
        // Vérification si l'utilisateur a la fonctionnalité "crawler"
        if (!$request->user()->hasFunctionality('crawler')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Validation de l'entrée
        $validatedData = $request->validate([
            'name' => 'required|string',  // Assurer un nom/prénom/pseudo valide
        ]);

        $name = $validatedData['name'];

        try {
            // Appel à l'API de SerpApi pour récupérer des informations sur une personne en France
            $response = Http::get('https://serpapi.com/search', [
                'api_key' => env('API_KEY_SERP'),
                'engine' => 'google',
                'q' => $name,  // Recherche par le nom/prénom/pseudo
                'google_domain' => 'google.fr',  // Domaine Google français
                'gl' => 'fr',  // Paramètre de localisation pour la France
                'hl' => 'fr',  // Langue française
            ]);

            // Vérification si la réponse est valide
            if ($response->failed()) {
                return response()->json(['error' => 'Erreur lors de la récupération des informations.'], 500);
            }

            // Extraire les résultats pertinents
            $data = $response->json();

            // Optionnel : Loguer l'action pour traçabilité
            Logs::create([
                'user_id' => auth()->id(),
                'target_id' => auth()->id(),
                'action' => auth()->user()->name . ' a effectué une recherche pour ' . $name,
                'functionality' => 'crawler'
            ]);

            // Retourner les résultats de l'API
            return response()->json([
                'message' => 'Informations récupérées avec succès.',
                'data' => $data,  // Vous pouvez personnaliser cette structure en fonction des informations que vous voulez afficher
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'appel à SerpApi: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne lors de la récupération des informations.'], 500);
        }
    }
}
