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

    public static function getQueryResumoProdutosClientes()
    {
        return "WITH ClientesMaisAtivos AS (
                    SELECT
                        cp.clientes_id,
                        COUNT(DISTINCT cp.pedidos_id) AS total_pedidos
                    FROM
                        produtos_pedidos cp
                    JOIN pedidos pe ON cp.pedidos_id = pe.id
                    WHERE
                        pe.data_pedido > current_date - INTERVAL '1 year'
                    GROUP BY
                        cp.clientes_id
                    ORDER BY
                        total_pedidos DESC
                    LIMIT 100
                ),

                ProdutosMaisCaros AS (
                    SELECT
                        p.id AS produto_id,
                        p.valor_unitario,
                        ROW_NUMBER() OVER (ORDER BY p.valor_unitario DESC) AS rank_valor
                    FROM
                        produtos p
                    WHERE
                        p.valor_unitario > (SELECT AVG(valor_unitario) FROM produtos)
                ),

                PedidosRecentes AS (
                    SELECT
                        cp.clientes_id,
                        cp.pedidos_id,
                        SUM(p.valor_unitario) AS total_pedido
                    FROM
                        produtos_pedidos cp
                    JOIN produtos p ON cp.produtos_id = p.id
                    WHERE
                        cp.produtos_id IN (SELECT produto_id FROM ProdutosMaisCaros WHERE rank_valor <= 50)
                    GROUP BY
                        cp.clientes_id, cp.pedidos_id
                )

                SELECT
                    cma.clientes_id,
                    c.nome,
                    c.endereco,
                    pr.pedidos_id,
                    pr.total_pedido,
                    pcm.produto_id,
                    p.descricao,
                    pcm.valor_unitario AS valor_produto,
                    pcm.rank_valor
                FROM
                    ClientesMaisAtivos cma
                JOIN clientes c ON cma.clientes_id = c.id
                JOIN PedidosRecentes pr ON cma.clientes_id = pr.clientes_id
                JOIN produtos_pedidos cp ON pr.pedidos_id = cp.pedidos_id AND pr.clientes_id = cp.clientes_id
                JOIN ProdutosMaisCaros pcm ON cp.produtos_id = pcm.produto_id
                JOIN produtos p ON pcm.produto_id = p.id
                ORDER BY
                    cma.total_pedidos DESC, pr.total_pedido DESC, pcm.rank_valor;";
    }
}
