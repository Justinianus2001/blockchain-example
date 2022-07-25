<?php

include_once 'vendor/autoload.php';

//  https://github.com/simplito/elliptic-php
use Elliptic\EC\KeyPair;
use Elliptic\EC;

$ec = new EC('secp256k1');

class Transaction
{
    private $fromAddress;
    private $toAddress;
    private $amount;
    private $signature;
    private $timestamp;

    public function __construct($fromAddress, $toAddress, $amount)
    {
        $this->fromAddress = $fromAddress;
        $this->toAddress = $toAddress;
        $this->amount = $amount;
        $this->timestamp = date("Y-m-d H:i:s");
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
        return hash("sha256", $this->fromAddress . $this->toAddress . $this->amount . $this->timestamp);
    }

    //  sign with private key to confirm transaction before add to block
    public function signTransaction(KeyPair $signingKey) : void
    {
        if ($signingKey->getPublic('hex') !== $this->fromAddress) {
            throw new \Exception("Cannot sign transactions for other wallets !", 406);
        }

        $hashTx = $this->calculateHash();
        $sig = $signingKey->sign($hashTx, 'base64');
        $this->signature = $sig->toDER('hex');
    }

    //  Check if transaction signed and valid with public key
    public function checkValidTransaction() : bool
    {
        global $ec;

        if (is_null($this->fromAddress)) {
            return true;
        }

        if (empty($this->signature)) {
            throw new \Exception("No signature in this transaction !", 404);
        }

        $publicKey = $ec->keyFromPublic($this->fromAddress, 'hex');
        return $publicKey->verify($this->calculateHash(), $this->signature);
    }
}