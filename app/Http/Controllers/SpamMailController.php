<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Logs;

class SpamMailController extends Controller
{
    /**
     * Send an email multiple times based on the given count.
     *
     * @OA\Post(
     *     path="/send-email",
     *     summary="Send an email to a recipient multiple times",
     *     tags={"Feature - Email Sending"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="The email address to send the message to",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="message",
     *         in="query",
     *         required=true,
     *         description="The message content to send",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="count",
     *         in="query",
     *         required=true,
     *         description="The number of times to send the email",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email successfully sent multiple times",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", description="Success message"),
     *             @OA\Property(property="count", type="integer", description="Number of emails sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Missing parameters",
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
     */
    public function sendEmail(Request $request)
    {
        // Vérifier si l'utilisateur a la fonctionnalité "spam_mail"
        if (!$request->user()->hasFunctionality('spam_mail')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité spam_mail !'], 403);
        }

        // Validation des données reçues
        $validatedData = $request->validate([
            'email' => 'required|email',
            'message' => 'required|string',
            'count' => 'required|integer',
        ]);

        $email = $validatedData['email'];
        $message = $validatedData['message'];
        $count = $validatedData['count'];

        // Loguer l'action dans la base de données
        Logs::create([
            'user_id' => auth()->id(),
            'target_id' => auth()->id(),
            'action' => auth()->user()->name . ' a envoyé un email à ' . $email . ' avec ' . $count . ' envois.',
            'functionality' => 'spam_mail'
        ]);

        // Initialiser un tableau pour stocker les résultats
        $responses = [];

        try {
            // Envoi de l'email 'count' fois
            for ($i = 0; $i < $count; $i++) {
                Mail::raw($message, function ($mail) use ($email) {
                    $mail->to($email)
                         ->from(config('mail.from.address'), config('mail.from.name'))
                         ->subject('Notification Email');
                });

                // Ajouter une entrée pour chaque itération
                $responses[] = [
                    'status' => 'success',
                    'message' => "Email envoyé à $email (Itération " . ($i + 1) . ")"
                ];
            }

            // Retourner la réponse JSON avec succès
            return response()->json([
                'status' => 'Email envoyé avec succès',
                'count' => $count,
                'results' => $responses
            ], 200);

        } catch (\Exception $e) {
            // En cas d'erreur, loguer l'erreur et retourner une réponse d'erreur
            return response()->json([
                'error' => 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage()
            ], 500);
        }
    }
}
