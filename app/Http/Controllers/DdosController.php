<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;
use Illuminate\Support\Facades\Log;

class DdosController extends Controller
{
    /**
     * @OA\Post(
     *     path="/ddos",
     *     summary="Simulate a DDoS attack by sending HTTP requests to an IP address with an optional port (maximum 30 requests)",
     *     tags={"Func - DDoS"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ip", type="string", description="The IP address (with optional port) to send requests to"),
     *             @OA\Property(property="count", type="integer", description="The number of times to send the request (maximum 30)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="DDoS simulation complete"),
     *     @OA\Response(response=400, description="Invalid input or too many requests"),
     *     @OA\Response(response=403, description="User does not have the necessary functionality"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Simulate a DDoS attack by sending HTTP requests to an IP address.
     */
    public function simulateDdos(Request $request)
    {
        // Ajouter cet en-tête pour forcer la réponse en JSON
        $request->headers->set('Accept', 'application/json');

        // Vérification si l'utilisateur a la fonctionnalité "ddos"
        if (!$request->user()->hasFunctionality('ddos')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Valider les données d'entrée
        $validatedData = $request->validate([
            'ip' => ['required', function ($attribute, $value, $fail) {
                // Valider l'IP et le port
                if (!filter_var(explode(':', $value)[0], FILTER_VALIDATE_IP)) {
                    $fail('Le champ ' . $attribute . ' doit être une adresse IP valide.');
                }
            }],
            'count' => 'required|integer'
        ]);

        $ipWithPort = $validatedData['ip'];
        $count = $validatedData['count'];

        if ($count > 30) {
            return response()->json(['error' => 'Le nombre de requêtes ne peut pas dépasser 30.'], 400);
        }

        // Séparer l'IP et le port
        $ipParts = explode(':', $ipWithPort);
        $ip = $ipParts[0];
        $port = isset($ipParts[1]) ? $ipParts[1] : 80; // Utiliser le port 80 par défaut si non fourni

        // Vérifier si l'IP est accessible via fsockopen
        $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
        if (!$connection) {
            return response()->json([
                'status' => 'error',
                'message' => "L'IP $ip:$port n'est pas accessible - $errstr"
            ], 400);
        }

        // Résultats des requêtes
        $responses = [];

        try {
            // Effectuer la requête HTTP basique, deux fois maximum
            for ($i = 0; $i < $count; $i++) {
                $url = 'http://' . $ip . ':' . $port;

                // Créer un contexte de flux HTTP
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 5,  // Limite de temps pour éviter que la requête traîne
                    ]
                ]);
                if($connection){    
                    // connexion à l'ip réussie, donc on test plusieurs types de requêtes pour 'surcharger l'ip'
                    // requête http
                    $stream = @fopen($url, 'r', false, $context);
                    // requête tcp
                    $stream = @fsockopen($ip, $port, $errno, $errstr, 5);
                    // Requête DNS (en utilisant gethostbyname)
                    $dnsResult = gethostbyname($ip);

                    $responses[] = [
                        'status' => 'success',
                        'message' => 'DDOS  : ' . $ip . ':' . $port
                    ];
                }
                
            }

            // Loguer l'action
            Logs::create([
                'user_id' => auth()->id(),
                'target_id' => auth()->id(),
                'action' => auth()->user()->name . ' a simulé un DDoS sur l\'IP ' . $ip . ':' . $port . ' avec ' . $count . ' requêtes',
                'functionality' => 'ddos'
            ]);

            // Réponse JSON avec les résultats des requêtes
            return response()->json([
                'message' => "Simulation de DDoS sur l'IP $ip avec $count requêtes réussie.",
                'results' => $responses
            ], 200);

        } catch (\Exception $e) {
            // En cas d'erreur
            return response()->json(['error' => 'Erreur lors de la requête : ' . $e->getMessage()], 500);
        }
    }
}
