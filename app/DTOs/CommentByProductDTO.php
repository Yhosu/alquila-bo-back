<?php

namespace App\DTOs;

use App\Models\Comment;

class CommentByProductDTO
{
    public function __construct(
        public string $user_name,
        public string $product_name,
        public string $text,
        public string $comment_date
    ) {}

    public static function fromModel(Comment $comment): self
    {
        return new self(
            user_name: $comment->user->name ?? '',
            product_name: $comment->product->name ?? '',
            text: $comment->text,
            comment_date: $comment->comment_date
        );
    }

    public static function fromCollection($comments): array
    {
        return $comments->map(fn($c) => self::fromModel($c))->toArray();
    }

    public function toArray(): array
    {
        return [
            'user_name'     => $this->user_name,
            'product_name'  => $this->product_name,
            'text'         => $this->text,
            'comment_date' => $this->comment_date,
        ];
    }
}
