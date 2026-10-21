<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Eletronico 
{
    /**
     * Retorna todos os eletrônicos ativos juntamente com a quantidade
     * em estoque e o indicador de estoque baixo.
     *
     * @return array
     */
    public static function listar(): array
    {
        $pdo = Database::getInstance();

        $sql = "
            SELECT 
                e.id_eletronico,
                e.nome,
                e.descricao,
                e.numero_lote,
                e.fabricante,
                e.categoria,
                e.tensao,
                e.dimensoes,
                e.resolucao,
                e.capacidade,
                e.conectividade,
                e.preco,
                e.processador,
                e.memoria,
                e.camera,
                e.sistema_operacional,
                e.cor,
                e.estoque_minimo,
                COALESCE(es.quantidade_atual, 0) AS quantidade_atual,
                CASE 
                    WHEN COALESCE(es.quantidade_atual, 0) <= e.estoque_minimo THEN 1 
                    ELSE 0 
                END AS estoque_baixo
            FROM eletronicos e
            LEFT JOIN estoque es ON e.id_eletronico = es.id_eletronico
            WHERE e.ativo = 1
            ORDER BY e.nome ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>