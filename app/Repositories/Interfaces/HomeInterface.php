<?php

namespace App\Repositories\Interfaces;

interface HomeInterface {
    public function getHome();
    public function getFaqs();
    public function getInformation();
    public function getProduct( string $id );
    public function registerSubscription( string $email, ?string $name = null);
    public function registerForm( string $userId, string $productId, date $initDate, date $finishDate, string $filters = '' );
}
