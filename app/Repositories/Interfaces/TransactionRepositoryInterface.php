<?php

namespace App\Repositories\Interfaces;

interface TransactionRepositoryInterface
{
    public function getTransactionsHistory(int $userId);
    public function createRecharge(array $data);
    public function createTransfer(array $data);
    public function getUserBalance(int $userId);
    public function updateUserBalance(int $userId, float $amount);
}
