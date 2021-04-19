<?php

declare(strict_types=1);


namespace App\Security\Secrets;


use function sodium_crypto_box_seal;

class SodiumEncrypter implements ValueEncryptInterface
{
    private string $encryptionKey;
    private string $decryptionKey;
    
    public function __construct(string $encryptionKeyPath, string $decryptionKeyPath)
    {
        $encryptionKey = require $encryptionKeyPath; // TODO: Prüfen, ob Performance bei Tests beeinträchtigt wird
        $decryptionKey = require $decryptionKeyPath;
        $this->encryptionKey = $encryptionKey;
        $this->decryptionKey = $decryptionKey;
    }
    
    public function decrypt(string $encryptedValue): string
    {
        return sodium_crypto_box_seal_open(base64_decode($encryptedValue), $this->decryptionKey);
    }
    
    public function encrypt(string $plainValue): string
    {
        return base64_encode(sodium_crypto_box_seal($plainValue, $this->encryptionKey));
    }
}
