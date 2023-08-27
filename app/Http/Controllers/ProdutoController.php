<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Helpers\ProdutoQueryHelper;

class ProdutoController extends Controller
{
    public function index()
    {
        $sql = ProdutoQueryHelper::getQueryHistoricoProdutosClientes();

        $resultados = DB::select($sql);

        return response()->json($resultados);
    }
}
