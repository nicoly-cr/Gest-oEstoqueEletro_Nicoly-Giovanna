<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

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

    /**
     * Cadastra um eletrônico, seu estoque inicial e o registro de movimentação inicial via Transação PDO.
     * 
     * @param array $dados
     * @return int ID do eletrônico inserido
     */
    public static function cadastrar(array $dados): int
    {
        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            // 1. Inserção na tabela eletronicos
            $sqlEletronico = "
                INSERT INTO eletronicos (
                    nome, descricao, numero_lote, fabricante, categoria, 
                    tensao, dimensoes, resolucao, capacidade, conectividade, 
                    preco, processador, memoria, camera, sistema_operacional, 
                    cor, estoque_minimo
                ) VALUES (
                    :nome, :descricao, :numero_lote, :fabricante, :categoria, 
                    :tensao, :dimensoes, :resolucao, :capacidade, :conectividade, 
                    :preco, :processador, :memoria, :camera, :sistema_operacional, 
                    :cor, :estoque_minimo
                )
            ";

            $stmt = $pdo->prepare($sqlEletronico);
            $stmt->execute([
                ':nome'                => $dados['nome'],
                ':descricao'           => $dados['descricao'] ?? '',
                ':numero_lote'         => $dados['numero_lote'],
                ':fabricante'          => $dados['fabricante'],
                ':categoria'           => $dados['categoria'],
                ':tensao'              => $dados['tensao'] ?? null,
                ':dimensoes'           => $dados['dimensoes'] ?? null,
                ':resolucao'           => $dados['resolucao'] ?? null,
                ':capacidade'          => $dados['capacidade'] ?? null,
                ':conectividade'       => $dados['conectividade'] ?? null,
                ':preco'               => $dados['preco'] ?? 0.00,
                ':processador'         => $dados['processador'] ?? null,
                ':memoria'             => $dados['memoria'] ?? null,
                ':camera'              => $dados['camera'] ?? null,
                ':sistema_operacional' => $dados['sistema_operacional'] ?? null,
                ':cor'                 => $dados['cor'] ?? null,
                ':estoque_minimo'      => $dados['estoque_minimo'] ?? 5
            ]);

            $idEletronico = (int) $pdo->lastInsertId();
            $estoqueInicial = (int) ($dados['estoque_inicial'] ?? 0);

            // 2. Inserção na tabela estoque
            $sqlEstoque = "
                INSERT INTO estoque (id_eletronico, quantidade_atual) 
                VALUES (:id_eletronico, :quantidade)
            ";
            $stmtEstoque = $pdo->prepare($sqlEstoque);
            $stmtEstoque->execute([
                ':id_eletronico' => $idEletronico,
                ':quantidade'   => $estoqueInicial
            ]);

            // 3. Inserção na tabela movimentacoes (Carga inicial)
            // id_usuario = 1 fixo por não haver autenticação
            $sqlMov = "
                INSERT INTO movimentacoes (
                    id_eletronico, id_usuario, tipo, quantidade, 
                    saldo_anterior, saldo_posterior, observacao
                ) VALUES (
                    :id_eletronico, 1, 'ENTRADA', :quantidade, 
                    0, :saldo_posterior, 'Carga inicial de cadastro'
                )
            ";
            $stmtMov = $pdo->prepare($sqlMov);
            $stmtMov->execute([
                ':id_eletronico'   => $idEletronico,
                ':quantidade'      => $estoqueInicial,
                ':saldo_posterior' => $estoqueInicial
            ]);

            $pdo->commit();

            return $idEletronico;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Busca os dados cadastrais e o estoque de um eletrônico específico pelo ID.
     *
     * @param int $id
     * @return array|false
     */
    public static function buscarPorId(int $id)
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
            WHERE e.id_eletronico = :id AND e.ativo = 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca eletrônicos ativos cujo nome contenha o termo informado.
     *
     * @param string $nome
     * @return array
     */
    public static function buscarPorNome(string $nome): array
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
                e.preco,
                COALESCE(es.quantidade_atual, 0) AS quantidade_atual,
                CASE 
                    WHEN COALESCE(es.quantidade_atual, 0) <= e.estoque_minimo THEN 1 
                    ELSE 0 
                END AS estoque_baixo
            FROM eletronicos e
            LEFT JOIN estoque es ON e.id_eletronico = es.id_eletronico
            WHERE e.nome LIKE :nome AND e.ativo = 1
            ORDER BY e.nome ASC
        ";

        $stmt = $pdo->prepare($sql);
        // Adiciona os curingas % para permitir busca por qualquer parte do nome
        $stmt->execute([':nome' => '%' . $nome . '%']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza os dados cadastrais de um eletrônico existente.
     *
     * @param array $dados
     * @return bool Retorna true se a alteração afetou alguma linha, ou false se o ID não existia
     */
    public static function atualizar(array $dados): bool
    {
        $pdo = Database::getInstance();

        $sql = "
            UPDATE eletronicos SET
                nome                = :nome,
                descricao           = :descricao,
                numero_lote         = :numero_lote,
                fabricante          = :fabricante,
                categoria           = :categoria,
                tensao              = :tensao,
                dimensoes           = :dimensoes,
                resolucao           = :resolucao,
                capacidade          = :capacidade,
                conectividade       = :conectividade,
                preco               = :preco,
                processador         = :processador,
                memoria             = :memoria,
                camera              = :camera,
                sistema_operacional = :sistema_operacional,
                cor                 = :cor,
                estoque_minimo      = :estoque_minimo
            WHERE id_eletronico = :id_eletronico AND ativo = 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_eletronico'       => $dados['id_eletronico'],
            ':nome'                => $dados['nome'],
            ':descricao'           => $dados['descricao'] ?? '',
            ':numero_lote'         => $dados['numero_lote'],
            ':fabricante'          => $dados['fabricante'],
            ':categoria'           => $dados['categoria'],
            ':tensao'              => $dados['tensao'] ?? null,
            ':dimensoes'           => $dados['dimensoes'] ?? null,
            ':resolucao'           => $dados['resolucao'] ?? null,
            ':capacidade'          => $dados['capacidade'] ?? null,
            ':conectividade'       => $dados['conectividade'] ?? null,
            ':preco'               => $dados['preco'] ?? 0.00,
            ':processador'         => $dados['processador'] ?? null,
            ':memoria'             => $dados['memoria'] ?? null,
            ':camera'              => $dados['camera'] ?? null,
            ':sistema_operacional' => $dados['sistema_operacional'] ?? null,
            ':cor'                 => $dados['cor'] ?? null,
            ':estoque_minimo'      => $dados['estoque_minimo'] ?? 5
        ]);

        // Retorna true se alterou alguma linha no banco
        return $stmt->rowCount() > 0;
    }

    
}

?>