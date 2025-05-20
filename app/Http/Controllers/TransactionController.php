<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="API Endpoints for managing transactions"
 * )
 */
class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="Get user's transactions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions",
     *         @OA\JsonContent(
     *             @OA\Property(property="transactions", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $transactions = $request->user()->transactions()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'transactions' => $transactions,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/recharge",
     *     tags={"Transactions"},
     *     summary="Recharge user's balance",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=1000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recharge successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recharge effectuée avec succès"),
     *             @OA\Property(property="transaction", type="object"),
     *             @OA\Property(property="new_balance", type="number", format="float")
     *         )
     *     )
     * )
     */
    public function recharge(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'type' => 'recharge',
                'amount' => $request->amount,
                'description' => 'Recharge de compte',
                'status' => 'completed',
            ]);

            $request->user()->increment('balance', $request->amount);

            DB::commit();

            return response()->json([
                'message' => 'Recharge effectuée avec succès',
                'transaction' => $transaction,
                'new_balance' => $request->user()->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la recharge',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/transfer",
     *     tags={"Transactions"},
     *     summary="Transfer money to another user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","recipient_phone"},
     *             @OA\Property(property="amount", type="number", format="float", example=500),
     *             @OA\Property(property="recipient_phone", type="string", example="+221777777777")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès"),
     *             @OA\Property(property="transaction", type="object"),
     *             @OA\Property(property="new_balance", type="number", format="float")
     *         )
     *     )
     * )
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'recipient_phone' => 'required|string|exists:users,phone',
        ]);

        if ($request->user()->phone === $request->recipient_phone) {
            return response()->json([
                'message' => 'Vous ne pouvez pas transférer à votre propre compte',
            ], 400);
        }

        if ($request->user()->balance < $request->amount) {
            return response()->json([
                'message' => 'Solde insuffisant',
            ], 400);
        }

        DB::beginTransaction();

        try {
            $recipient = User::where('phone', $request->recipient_phone)->first();

            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'type' => 'transfer',
                'amount' => $request->amount,
                'description' => 'Transfert d\'argent',
                'recipient_phone' => $request->recipient_phone,
                'status' => 'completed',
            ]);

            $request->user()->decrement('balance', $request->amount);
            $recipient->increment('balance', $request->amount);

            DB::commit();

            return response()->json([
                'message' => 'Transfert effectué avec succès',
                'transaction' => $transaction,
                'new_balance' => $request->user()->balance,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors du transfert',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/balance",
     *     tags={"Transactions"},
     *     summary="Get user's current balance",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current balance",
     *         @OA\JsonContent(
     *             @OA\Property(property="balance", type="number", format="float")
     *         )
     *     )
     * )
     */
    public function balance(Request $request)
    {
        return response()->json([
            'balance' => $request->user()->balance,
        ]);
    }
}
