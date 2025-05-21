<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="API Endpoints for managing transactions, transfers and recharges"
 * )
 */
class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="Get user's transaction history",
     *     description="Retrieve all transactions (recharges, sent and received transfers) for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Transaction history",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="type", type="string", example="recharge"),
     *                         @OA\Property(property="amount", type="number", format="float", example=10000),
     *                         @OA\Property(property="provider", type="string", example="Wave"),
     *                         @OA\Property(property="date", type="string", format="date-time", example="2025-05-18 10:15")
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="type", type="string", example="sent"),
     *                         @OA\Property(property="amount", type="number", format="float", example=3000),
     *                         @OA\Property(property="to", type="object",
     *                             @OA\Property(property="first_name", type="string", example="Awa"),
     *                             @OA\Property(property="last_name", type="string", example="Diop"),
     *                             @OA\Property(property="phone_number", type="string", example="771234567")
     *                         ),
     *                         @OA\Property(property="date", type="string", format="date-time", example="2025-05-17 15:40")
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="type", type="string", example="received"),
     *                         @OA\Property(property="amount", type="number", format="float", example=5000),
     *                         @OA\Property(property="from", type="object",
     *                             @OA\Property(property="first_name", type="string", example="Moussa"),
     *                             @OA\Property(property="last_name", type="string", example="Sow"),
     *                             @OA\Property(property="phone_number", type="string", example="770123456")
     *                         ),
     *                         @OA\Property(property="date", type="string", format="date-time", example="2025-05-16 08:00")
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getTransactionsHistory(Request $request)
    {
        return response()->json(
            $this->transactionService->getTransactionsHistory($request->user()->id)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/recharge",
     *     tags={"Transactions"},
     *     summary="Recharge user's balance",
     *     description="Add funds to user's account through a payment provider",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","provider"},
     *             @OA\Property(property="amount", type="number", format="float", example=5000, description="Amount to recharge"),
     *             @OA\Property(property="provider", type="string", example="Wave", description="Payment provider name"),
     *             @OA\Property(property="transaction_id", type="string", example="WAVETX123456", description="Provider's transaction ID (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recharge successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recharge effectuée avec succès."),
     *             @OA\Property(property="new_balance", type="string", example="17000.00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function recharge(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'provider' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
        ]);

        try {
            $result = $this->transactionService->recharge($request->user()->id, $validated);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la recharge.'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/transfer",
     *     tags={"Transactions"},
     *     summary="Transfer money to another user",
     *     description="Send money to another user using their phone number",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","recipient_phone"},
     *             @OA\Property(property="amount", type="number", format="float", example=500, description="Amount to transfer"),
     *             @OA\Property(property="recipient_phone", type="string", example="+221777777777", description="Recipient's phone number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès"),
     *             @OA\Property(property="new_balance", type="number", format="float", example=15000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solde insuffisant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'recipient_phone' => 'required|string|exists:users,phone_number',
        ]);

        try {
            $result = $this->transactionService->transfer(
                $request->user()->id,
                $request->recipient_phone,
                $request->amount
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/balance",
     *     tags={"Transactions"},
     *     summary="Get user's current balance",
     *     description="Retrieve the current balance of the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="balance", type="number", format="float", example=15000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function balance(Request $request)
    {
        return response()->json(
            $this->transactionService->getBalance($request->user()->id)
        );
    }
}
