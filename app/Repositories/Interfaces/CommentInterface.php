<?php

namespace App\Repositories\Interfaces;

interface CommentInterface {
    public function registerComment( string $userId, string $productId, string $text );
    public function getCommentsByProduct( string $productId );
}
