<?php

namespace App\Helpers;

class ProdutoQueryHelper
{
    public static function getQueryHistoricoProdutosClientes(): string
    {
        return "
            WITH total_gasto AS (
                SELECT date_trunc('month', pd.data_pedido) AS mes, c.id AS cliente_id, c.nome AS cliente_nome,
                       SUM(p.valor_unitario) AS total
                FROM clientes c
                JOIN produtos_pedidos pp ON c.id = pp.clientes_id
                JOIN pedidos pd ON pp.pedidos_id = pd.id
                JOIN produtos p ON pp.produtos_id = p.id
                GROUP BY mes, cliente_id, cliente_nome
            ),

            produto_mais_comprado AS (
                SELECT date_trunc('month', pd.data_pedido) AS mes, c.id AS cliente_id, p.descricao AS produto_descricao,
                       COUNT(*) AS qtd
                FROM clientes c
                JOIN produtos_pedidos pp ON c.id = pp.clientes_id
                JOIN pedidos pd ON pp.pedidos_id = pd.id
                JOIN produtos p ON pp.produtos_id = p.id
                GROUP BY mes, cliente_id, produto_descricao
            )

            SELECT tg.mes, tg.cliente_nome, tg.total, pmc.produto_descricao
            FROM total_gasto tg
            JOIN (
            SELECT mes, cliente_id, MAX(total) as max_total
            FROM total_gasto
            GROUP BY mes, cliente_id
            ) top_gasto ON tg.mes = top_gasto.mes AND tg.cliente_id = top_gasto.cliente_id AND tg.total = top_gasto.max_total
            JOIN produto_mais_comprado pmc ON tg.mes = pmc.mes AND tg.cliente_id = pmc.cliente_id
            WHERE (
            SELECT COUNT(*)
            FROM total_gasto
            WHERE mes = tg.mes AND total > tg.total
            ) < 10
            ORDER BY tg.mes, tg.total DESC;
        ";
    }
}
