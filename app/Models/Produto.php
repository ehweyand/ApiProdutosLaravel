<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    public function produtosPedidos()
    {
        return $this->hasMany(ProdutosPedido::class, 'produtos_id');
    }
}
