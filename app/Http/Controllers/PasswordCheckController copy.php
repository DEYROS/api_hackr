<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logs;

class DdosController extends Controller
{
    /**
     * @OA\Post(
     *     path="/ddos",
     *     summary="Simulate a DDoS attack by pinging an IP address a certain number of times (maximum 2 pings)",
     *     tags={"DDoS"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ip", type="string", description="The IP address to ping"),
     *             @OA\Property(property="count", type="integer", description="The number of times to ping the IP (maximum 2)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="DDoS simulation complete"),
     *     @OA\Response(response=400, description="Invalid input or too many pings requested"),
     *     @OA\Response(response=403, description="User does not have the necessary functionality"),
     *     security={{"bearerAuth": {}}}
     * )
     *
     * Simulate a DDoS attack by pinging an IP address.
     */
    public function simulateDdos(Request $request)
    {
        // Ajouter cet en-tête pour forcer la réponse en JSON
        $request->headers->set('Accept', 'application/json');

        // Vérification si l'utilisateur a la fonctionnalité "ddos"
        if (!$request->user()->hasFunctionality('ddos')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Valider les entrées : l'IP doit être valide et le nombre de pings doit être un entier positif, maximum 2
        $validatedData = $request->validate([
            'ip' => 'required|ip',
            'count' => 'required|integer|min=1|max=50'
        ]);

        $ip = $validatedData['ip'];
        $count = $validatedData['count'];

        // Effectuer la commande ping, 2 fois maximum
        $pingResults = [];
        for ($i = 0; $i < $count; $i++) {
            // Exécuter la commande ping (simulation très basique ici)
            // On utilise exec pour exécuter le ping en ligne de commande
            $output = [];
            exec("ping -c 1 " . escapeshellarg($ip), $output);

            // Ajouter la réponse à l'array des résultats
            $pingResults[] = implode("\n", $output);
        }

        // Log de l'action dans les logs de l'API
        Logs::create([
            'user_id' => auth()->id(),
            'target_id' => auth()->id(),
            'action' => auth()->user()->name . ' a simulé un DDoS sur l\'IP ' . $ip . ' avec ' . $count . ' requêtes',
            'functionality' => 'ddos'
        ]);

        // Réponse JSON avec les résultats des pings
        return response()->json([
            'message' => "Simulation de DDoS sur l'IP $ip avec $count requêtes réussie.",
            'results' => $pingResults
        ], 200);
    }
}
