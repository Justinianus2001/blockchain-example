<?php

include_once 'vendor/autoload.php';

//  https://github.com/simplito/elliptic-php
use Elliptic\EC;

$ec = new EC('secp256k1');

$key = $ec->genKeyPair();

$publicKey = $key->getPublic('hex');
$privateKey = $key->getPrivate('hex');

print_r("Generate successful !<br>");
print_r("Public Key: $publicKey<br>");
print_r("Private Key: $privateKey<br>");