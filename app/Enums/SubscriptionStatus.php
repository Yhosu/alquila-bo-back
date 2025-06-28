<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case PENDIENTE = 'pendiente';
    case CONFIRMADO = 'confirmado';
    case CANCELADO = 'cancelado';
}
