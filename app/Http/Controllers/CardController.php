<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Card",
 *     description="API Endpoints for virtual card management"
 * )
 */
class CardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/card",
     *     tags={"Card"},
     *     summary="Get user's virtual card information",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Card information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="phone_number", type="string", example="+221777777777"),
     *                 @OA\Property(property="balance", type="string", example="12000.00"),
     *                 @OA\Property(property="card_number", type="string", example="CARD-1-8472"),
     *                 @OA\Property(property="created_at", type="string", format="date", example="2025-05-18")
     *             )
     *         )
     *     )
     * )
     */
    public function getCardInfo(Request $request)
    {
        $user = $request->user();

        // Générer un numéro de carte unique basé sur l'ID de l'utilisateur
        $cardNumber = 'CARD-' . $user->id . '-' . strtoupper(Str::random(4));

        return response()->json([
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone_number,
                'balance' => number_format($user->balance, 2, '.', ''),
                'card_number' => $cardNumber,
                'created_at' => $user->created_at->format('Y-m-d')
            ]
        ]);
    }
}
