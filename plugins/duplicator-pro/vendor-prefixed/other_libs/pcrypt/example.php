<?php

namespace VendorDuplicator;

require 'class.pcrypt.php';
/* 
MODE: MODE_ECB or MODE_CBC
ALGO: BLOWFISH
KEY:  Your secret key :) (max lenght: 56)
*/
$crypt = new pcrypt(\MODE_ECB, "BLOWFISH", "secretkey");
// to encrypt
$plaintext = "password";
$ciphertext = $crypt->encrypt($plaintext);
// to decrypt
$decrypted = $crypt->decrypt($ciphertext);
echo $plaintext . "<br />" . $ciphertext . "<br />" . $decrypted;
