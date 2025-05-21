<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\TransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function getTransactionsHistory(int $userId)
    {
        return $this->transactionRepository->getTransactionsHistory($userId);
    }

    public function recharge(int $userId, array $data)
    {
        try {
            DB::beginTransaction();

            $recharge = $this->transactionRepository->createRecharge([
                'user_id' => $userId,
                'amount' => $data['amount'],
                'provider' => $data['provider'],
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);

            $this->transactionRepository->updateUserBalance($userId, $data['amount']);

            DB::commit();

            return [
                'message' => 'Recharge effectuée avec succès.',
                'new_balance' => number_format($this->transactionRepository->getUserBalance($userId), 2, '.', '')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function transfer(int $senderId, string $recipientPhone, float $amount)
    {
        $recipient = User::where('phone_number', $recipientPhone)->firstOrFail();

        if ($senderId === $recipient->id) {
            throw new \Exception('Vous ne pouvez pas transférer à votre propre compte');
        }

        if ($this->transactionRepository->getUserBalance($senderId) < $amount) {
            throw new \Exception('Solde insuffisant');
        }

        try {
            DB::beginTransaction();

            $transfer = $this->transactionRepository->createTransfer([
                'sender_id' => $senderId,
                'recipient_id' => $recipient->id,
                'amount' => $amount,
            ]);

            $this->transactionRepository->updateUserBalance($senderId, -$amount);
            $this->transactionRepository->updateUserBalance($recipient->id, $amount);

            DB::commit();

            return [
                'message' => 'Transfert effectué avec succès',
                'new_balance' => $this->transactionRepository->getUserBalance($senderId),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getBalance(int $userId)
    {
        return [
            'balance' => $this->transactionRepository->getUserBalance($userId),
        ];
    }
}
