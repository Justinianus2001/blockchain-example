<?php

require_once "Blockchain.php";
require_once "Transaction.php";

//  https://github.com/simplito/elliptic-php
use Elliptic\EC;

$ec = new EC('secp256k1');
$myKey = $ec->keyFromPrivate("ab605f753f196f68538aac0b17c224e4c034a846f8689711f23613b1fadc034d");
$myWalletAddress = $myKey->getPublic('hex');

$bc = new Blockchain();

$bc->minePendingTransactions($myWalletAddress);

$tx1 = new Transaction($myWalletAddress, 'address2', 100);
$tx1->signTransaction($myKey);
$bc->addNewTransaction($tx1);

$bc->minePendingTransactions($myWalletAddress);

$tx2 = new Transaction($myWalletAddress, 'address1', 50);
$tx2->signTransaction($myKey);
$bc->addNewTransaction($tx2);

$bc->minePendingTransactions($myWalletAddress);
print_r("$myWalletAddress: " . $bc->getBalanceOfAddress($myWalletAddress) . "<br>");

print_r("<pre>");
print_r($bc);
print_r("</pre>");

print_r("Is valid blockchain: " . $bc->checkValidChain() ? "YES" : "NO");