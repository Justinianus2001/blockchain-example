<?php

require_once "Block.php";

class Blockchain
{
    private const DIFFICULT = 4;
    private const REWARD = 100;
    private $chain;
    private $pendingTransactions;

    public function __construct()
    {
        $this->chain = [$this->createGenesisBlock()];
        $this->pendingTransactions = [];
    }

    //  Generate the first block (block 0) for blockchain
    public function createGenesisBlock() : Block
    {
        return new Block(date("Y-m-d H:i:s"), [], str_repeat("0", 64));
    }

    //  Get first block of blockchain
    public function getOldestBlock() : Block
    {
        return reset($this->chain);
    }

    //  Get last block of blockchain
    public function getLatestBlock() : Block
    {
        return end($this->chain);
    }

    //  Start mining the hash answer and get the reward (crypto currency) into wallet
    public function minePendingTransactions(string $mineRewardAddress) : void
    {
        $this->pendingTransactions[] = new Transaction("COINBASE", $mineRewardAddress, Blockchain::REWARD);

        $block = new Block(date("Y-m-d H:i:s"), $this->pendingTransactions, $this->getLatestBlock()->hash);
        $block->mineBlock(Blockchain::DIFFICULT);

        $block->calculateHash();
        $this->chain[] = $block;
        print_r("Add block successful!<br>");

        $this->pendingTransactions = [];
    }

    //  Check if transaction is valid before add into pending transactions list
    public function addNewTransaction(Transaction $transaction) : void
    {
        if (!$transaction->fromAddress || !$transaction->toAddress) {
            throw new \Exception("Transaction must have from address and to address !", 406);
        }

        if (!$transaction->checkValidTransaction()) {
            throw new \Exception("Cannot add invalid transaction into chain !", 406);
        }

        if ($transaction->amount <= 0) {
            throw new \Exception("Transaction amount should be positive !", 406);
        }

        $walletBalance = $this->getBalanceOfAddress($transaction->fromAddress);
        if ($walletBalance < $transaction->amount) {
            throw new \Exception("Wallet not have enough balance !", 406);
        }

        $pendingTxForWallet = array_filter($this->pendingTransactions, function ($pendingTransaction) use ($transaction) {
            return $pendingTransaction->fromAddress === $transaction->fromAddress;
        });

        if (count($pendingTxForWallet) > 0) {
            $totalPendingAmount = 0;
            foreach ($pendingTxForWallet as $tx) {
                $totalPendingAmount += $tx->amount;
            }

            $totalAmount = $totalPendingAmount + $transaction->amount;
            if ($totalAmount > $walletBalance) {
                throw new \Exception("Pending transaction amount exceeds this wallet's balance !", 406);
            }
        }

        $this->pendingTransactions[] = $transaction;
    }

    //  Get current banlance of wallet by address
    public function getBalanceOfAddress(string $address) : int
    {
        $balance = 0;

        foreach ($this->chain as $block) {
            foreach ($block->transactions as $transaction) {
                if ($transaction->fromAddress === $address) {
                    $balance -= $transaction->amount;
                }
                if ($transaction->toAddress === $address) {
                    $balance += $transaction->amount;
                }
            }
        }

        return $balance;
    }

    //  Get all transaction related to the wallet address
    public function getAllTransactionsForWallet(string $address) : array
    {
        $txs = [];

        foreach ($this->chain as $block) {
            if (isset($block->transactions)) {
                foreach ($block->transactions as $tx) {
                    if ($tx->fromAddress === $address || $tx->toAddress === $address) {
                        $txs[] = $tx;
                    }
                }
            }
        }

        return $txs;
    }

    //  Check if current blockchain is valid
    public function checkValidChain() : bool
    {
        if ($this->createGenesisBlock() !== $this->getOldestBlock()) {
            return false;
        }

        for ($i = 1; $i < count($this->chain); $i ++) {
            $curBlock = $this->chain[$i];
            $prevBlock = $this->chain[$i - 1];

            if ($curBlock->hash != $curBlock->calculateHash()
            || $curBlock->previousHash != $prevBlock->hash
            || !$curBlock->hasValidTransactions()) {
                return false;
            }
        }

        return true;
    }
}