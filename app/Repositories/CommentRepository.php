<?php

namespace App\Repositories;
use App\Exceptions\BadRequestException;
use App\DTOs\CommentRegisterDTO;
use App\Repositories\Interfaces\CommentInterface;
use App\DTOs\CommentByProductDTO;
use Throwable;

class CommentRepository implements CommentInterface
{
    protected $modelComment  = \App\Models\Comment::class;

    public function registerComment( $userId, $productId, $text) {
        try {
            $product = \App\Models\Product::find( $productId );
            if( !$product ) throw new BadRequestException("Hubo un error al buscar su producto", ['No se encuentra el producto asociado al id ingresado.']);
            $comment = $this->modelComment::create([
                'user_id'       => $userId,
                'product_id'    => $productId,
                'text'          => $text,
                'comment_date'  => now()
            ]);
            return CommentRegisterDTO::fromModel($comment);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getCommentsByProduct( $productId ) {
        try {
            $commetsByProductUser = \App\Models\Comment::with([
                'user:id,name',
                'product:id,name,image'
                /*'product' => function($query) {
                    $query->select('id','name','image');
                }*/
            ])
            ->select('id','user_id','product_id','text','comment_date')
            ->where('enabled', 1)
            ->where('product_id', $productId)
            ->orderBy('comment_date', 'DESC')
            ->get();

            \Log::info($commetsByProductUser);
            return CommentByProductDTO::fromCollection($commetsByProductUser);
                //return $commetsByProductUser;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}
