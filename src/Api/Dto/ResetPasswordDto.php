<?php

declare(strict_types=1);


namespace App\Api\Dto;


class ResetPasswordDto
{
    public string $token;
    public string $newPassword;
}
