<?php

namespace App\Repositories\Interfaces;

interface HomeInterface {
    public function getHome();
    public function getFaqs();
    // public function getInformation();
    public function getProduct( string $id );
    public function registerSubscription( string $email, ?string $name = null);
    public function confirmSubscription(string $tokenConfirmSubscription);
    public function cancelSubscription(string $tokenCancelSubscription);
    public function registerForm( string $userId, string $productId, date $initDate, date $finishDate, string $filters = '' );
    public function getCompaniesMap();
    public function registerComment( string $userId, string $productId, string $text);

}
