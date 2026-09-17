<?php

namespace App\Exceptions;

use RuntimeException;

class PaymentNotConfiguredException extends RuntimeException
{
    public function __construct(string $message = 'Ödeme sağlayıcısı henüz yapılandırılmamıştır.')
    {
        parent::__construct($message);
    }
}
