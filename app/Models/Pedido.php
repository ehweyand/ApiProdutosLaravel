<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    public function produtosPedidos()
    {
        return $this->hasMany(ProdutosPedido::class, 'pedidos_id');
    }
}
