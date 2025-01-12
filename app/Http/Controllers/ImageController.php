<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Logs;

class ImageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/random-image",
     *     summary="Download a random image",
     *     tags={"Func - Random Image"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Image downloaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image downloaded successfully!"),
     *             @OA\Property(property="url", type="string", example="storage/random-images/1673639203948.jpg")
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
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error while downloading the image")
     *         )
     *     )
     * )
     *
     * Download a random image from "thispersondoesnotexist.com".
     */
    public function getRandomImage(Request $request)
    {
        // Vérification si l'utilisateur a la fonctionnalité "random_image"
        if (!$request->user()->hasFunctionality('random_image')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        $imageUrl = 'https://thispersondoesnotexist.com';
        try {
            // Téléchargement de l'image en tant que données binaires
            $response = Http::get($imageUrl);
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch image from source');
            }

            // Générer un nom de fichier unique
            $imageName = time() . '.jpg';
            $publicPath = "random-images/{$imageName}";

            // Sauvegarder l'image dans le dossier public
            $absolutePath = public_path($publicPath);
            file_put_contents($absolutePath, $response->body());

            // Loguer l'action de téléchargement
            Logs::create([
                'user_id' => auth()->id(),
                'target_id' => auth()->id(),
                'action' => auth()->user()->name . ' downloaded a random image',
                'functionality' => 'random_image'
            ]);

            // Retourner l'URL publique de l'image
            return response()->json([
                'message' => 'Image downloaded successfully!',
                'url' => asset($publicPath)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error while downloading the image: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error while downloading the image',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
