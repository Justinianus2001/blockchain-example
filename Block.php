<?php

class Block
{
    private $timestamp;
    private $transactions;
    private $previousHash;
    private $hash;
    private $nonce;

    public function __construct(string $timestamp, $transactions, string $previousHash = "")
    {
        $this->timestamp = $timestamp;
        $this->transactions = $transactions;
        $this->previousHash = $previousHash;
        $this->hash = $this->calculateHash();
        $this->nonce = 0;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new \Exception("Property not found !", 404);
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        } else {
            throw new \Exception("Property not found !", 404);
        }
    }

    public function calculateHash() : string
    {
        return hash("sha256", $this->previousHash . $this->timestamp . json_encode($this->transactions) . $this->nonce);
    }

    //  Find hash answer start with K (difficult) characters '0' by increase nonce value
    public function mineBlock(int $difficult) : void
    {
        while (mb_substr($this->hash, 0, $difficult) !== str_repeat("0", $difficult)) {
            $this->nonce ++;
            $this->hash = $this->calculateHash();
        }

        print_r("Mine successful! Hash: $this->hash, nonce: $this->nonce<br>");
    }

    //  Loop each transaction to check if all is valid
    public function hasValidTransactions() : bool
    {
        foreach ($this->transactions as $tx) {
            if (!$tx->checkValidTransaction()) {
                return false;
            }
        }
        return true;
    }
}