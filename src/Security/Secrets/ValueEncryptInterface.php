<?php

declare(strict_types=1);


namespace App\Security\Secrets;


interface ValueEncryptInterface
{
    public function encrypt(string $plainValue): string;
    public function decrypt(string $encryptedValue): string;
}
