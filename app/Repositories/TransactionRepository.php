<?php

namespace App\Repositories;

use App\Models\Recharge;
use App\Models\Transfer;
use App\Models\User;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Collection;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function getTransactionsHistory(int $userId): Collection
    {
        // Récupérer les recharges
        $recharges = Recharge::where('user_id', $userId)
            ->select('amount', 'provider', 'created_at')
            ->get()
            ->map(function ($recharge) {
                return [
                    'type' => 'recharge',
                    'amount' => $recharge->amount,
                    'provider' => $recharge->provider,
                    'date' => $recharge->created_at->format('Y-m-d H:i')
                ];
            });

        // Récupérer les transferts envoyés
        $sentTransfers = Transfer::where('sender_id', $userId)
            ->with('recipient:id,first_name,last_name,phone_number')
            ->get()
            ->map(function ($transfer) {
                return [
                    'type' => 'sent',
                    'amount' => $transfer->amount,
                    'to' => [
                        'first_name' => $transfer->recipient->first_name,
                        'last_name' => $transfer->recipient->last_name,
                        'phone_number' => $transfer->recipient->phone_number
                    ],
                    'date' => $transfer->created_at->format('Y-m-d H:i')
                ];
            });

        // Récupérer les transferts reçus
        $receivedTransfers = Transfer::where('recipient_id', $userId)
            ->with('sender:id,first_name,last_name,phone_number')
            ->get()
            ->map(function ($transfer) {
                return [
                    'type' => 'received',
                    'amount' => $transfer->amount,
                    'from' => [
                        'first_name' => $transfer->sender->first_name,
                        'last_name' => $transfer->sender->last_name,
                        'phone_number' => $transfer->sender->phone_number
                    ],
                    'date' => $transfer->created_at->format('Y-m-d H:i')
                ];
            });

        return $recharges->concat($sentTransfers)->concat($receivedTransfers)
            ->sortByDesc('date')
            ->values();
    }

    public function createRecharge(array $data): Recharge
    {
        return Recharge::create($data);
    }

    public function createTransfer(array $data): Transfer
    {
        return Transfer::create($data);
    }

    public function getUserBalance(int $userId): float
    {
        return User::findOrFail($userId)->balance;
    }

    public function updateUserBalance(int $userId, float $amount): void
    {
        $user = User::findOrFail($userId);
        $user->balance += $amount;
        $user->save();
    }
}
