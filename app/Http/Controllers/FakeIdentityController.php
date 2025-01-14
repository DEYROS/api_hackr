<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Faker\Factory as Faker;
use App\Models\Logs;
use Illuminate\Support\Facades\Log;

class FakeIdentityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/fake-identity",
     *     summary="Generate a fake identity",
     *     tags={"Func - Fake Identity"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Fake identity generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="+1-202-555-0125"),
     *             @OA\Property(property="address", type="string", example="1234 Elm Street, Springfield, IL 62701"),
     *             @OA\Property(property="jobTitle", type="string", example="Software Developer"),
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
     *             @OA\Property(property="error", type="string", example="Error while generating the fake identity")
     *         )
     *     )
     * )
     *
     * Generate a fake identity using the Faker library.
     */
    public function generateFakeIdentity(Request $request)
    {
        // Vérification si l'utilisateur a la fonctionnalité "fake_identity"
        if (!$request->user()->hasFunctionality('fake_identity')) {
            return response()->json(['error' => 'Tu ne peux pas faire ça, il te manque la fonctionnalité !'], 403);
        }

        try {
            // Initialisation de Faker
            $faker = Faker::create();

            // Génération d'une identité fictive
            $fakeIdentity = [
                'name' => $faker->name,
                'email' => $faker->email,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'jobTitle' => $faker->jobTitle,
            ];

            // Loguer l'action
            Logs::create([
                'user_id' => auth()->id(),
                'target_id' => auth()->id(),
                'action' => auth()->user()->name . ' a généré une identité fictive',
                'functionality' => 'fake_identity'
            ]);

            return response()->json([
                'message' => 'Fake identity generated successfully!',
                'data' => $fakeIdentity,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error while generating the fake identity: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error while generating the fake identity',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
