<?php

namespace App\DTOs;

use App\Models\Comment;

class CommentRegisterDTO
{
    public function __construct(
        public string $text,
        public string $comment_date,
    ) {}

    public static function fromModel(Comment $comment): self
    {
        return new self(
            text: $comment->text,
            comment_date: $comment->comment_date
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'comment_date' => $this->comment_date
        ];
    }
}
