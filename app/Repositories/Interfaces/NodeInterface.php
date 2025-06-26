<?php

namespace App\Repositories\Interfaces;

interface NodeInterface {
    public function getListOfNode( string $node, bool $paginate, array $filters );
}
