<?php

namespace App\Repositories\Interfaces;

interface HomeInterface {
    public function getHome();
    public function getFaqs();
    public function registerSubscription( string $email, ?string $name = null);
}
