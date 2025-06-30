<?php

namespace App\Repositories\Interfaces;

interface HomeInterface {
    public function getHome();
    public function getFaqs();
    public function getInformation();
    public function getProduct( string $id );

}
