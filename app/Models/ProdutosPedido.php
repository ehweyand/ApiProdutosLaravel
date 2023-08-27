<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutosPedido extends Model
{
    use HasFactory;

    protected $table = 'produtos_pedidos';

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produtos_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedidos_id');
    }
}
