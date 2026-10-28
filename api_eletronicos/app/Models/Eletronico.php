<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Eletronico 
{
    /**
     * Lista todos os eletrônicos ativos com os seus respetivos saldos de estoque.
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
                COALESCE(est.quantidade_atual, 0) AS quantidade_atual,
                CASE 
                    WHEN COALESCE(est.quantidade_atual, 0) <= e.estoque_minimo THEN 1
                    ELSE 0
                END AS estoque_baixo
            FROM eletronicos e
            LEFT JOIN estoque est ON e.id_eletronico = est.id_eletronico
            WHERE e.ativo = 1
            ORDER BY e.nome ASC
        ";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca um eletrônico ativo específico pelo seu ID.
     *
     * @param int $id
     * @return array|null
     */
    public static function buscarPorId(int $id): ?array
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
                COALESCE(est.quantidade_atual, 0) AS quantidade_atual,
                CASE 
                    WHEN COALESCE(est.quantidade_atual, 0) <= e.estoque_minimo THEN 1
                    ELSE 0
                END AS estoque_baixo
            FROM eletronicos e
            LEFT JOIN estoque est ON e.id_eletronico = est.id_eletronico
            WHERE e.id_eletronico = :id AND e.ativo = 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resultado ?: null;
    }

    /**
     * Busca eletrônicos ativos pelo nome (busca parcial / LIKE).
     *
     * @param string $termo
     * @return array
     */
    public static function buscarPorNome(string $termo): array
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
                COALESCE(est.quantidade_atual, 0) AS quantidade_atual,
                CASE 
                    WHEN COALESCE(est.quantidade_atual, 0) <= e.estoque_minimo THEN 1
                    ELSE 0
                END AS estoque_baixo
            FROM eletronicos e
            LEFT JOIN estoque est ON e.id_eletronico = est.id_eletronico
            WHERE e.nome LIKE :termo AND e.ativo = 1
            ORDER BY e.nome ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':termo' => "%{$termo}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cadastra um novo eletrônico e cria o seu registro de estoque inicial.
     *
     * @param array $dados
     * @return int ID do produto inserido
     */
    public static function cadastrar(array $dados): int
    {
        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            $sql = "
                INSERT INTO eletronicos (
                    nome, descricao, numero_lote, fabricante, categoria, 
                    tensao, dimensoes, resolucao, capacidade, conectividade, 
                    preco, processador, memoria, camera, sistema_operacional, 
                    cor, estoque_minimo, ativo
                ) VALUES (
                    :nome, :descricao, :numero_lote, :fabricante, :categoria, 
                    :tensao, :dimensoes, :resolucao, :capacidade, :conectividade, 
                    :preco, :processador, :memoria, :camera, :sistema_operacional, 
                    :cor, :estoque_minimo, 1
                )
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome'                => $dados['nome'],
                ':descricao'           => $dados['descricao'] ?? null,
                ':numero_lote'         => $dados['numero_lote'] ?? null,
                ':fabricante'          => $dados['fabricante'] ?? null,
                ':categoria'           => $dados['categoria'] ?? null,
                ':tensao'              => $dados['tensao'] ?? null,
                ':dimensoes'           => $dados['dimensoes'] ?? null,
                ':resolucao'           => $dados['resolucao'] ?? null,
                ':capacidade'          => $dados['capacidade'] ?? null,
                ':conectividade'       => $dados['conectividade'] ?? null,
                ':preco'               => $dados['preco'],
                ':processador'         => $dados['processador'] ?? null,
                ':memoria'             => $dados['memoria'] ?? null,
                ':camera'              => $dados['camera'] ?? null,
                ':sistema_operacional' => $dados['sistema_operacional'] ?? null,
                ':cor'                 => $dados['cor'] ?? null,
                ':estoque_minimo'      => $dados['estoque_minimo'] ?? 0
            ]);

            $idEletronico = (int) $pdo->lastInsertId();

            // Cria a linha inicial na tabela estoque
            $sqlEstoque = "INSERT INTO estoque (id_eletronico, quantidade_atual) VALUES (:id, :qtd)";
            $stmtEstoque = $pdo->prepare($sqlEstoque);
            $stmtEstoque->execute([
                ':id'  => $idEletronico,
                ':qtd' => $dados['quantidade_inicial'] ?? 0
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
     * Atualiza os dados de um eletrônico existente.
     *
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public static function atualizar(int $id, array $dados): bool
    {
        $pdo = Database::getInstance();

        $sql = "
            UPDATE eletronicos SET
                nome = :nome,
                descricao = :descricao,
                numero_lote = :numero_lote,
                fabricante = :fabricante,
                categoria = :categoria,
                tensao = :tensao,
                dimensoes = :dimensoes,
                resolucao = :resolucao,
                capacidade = :capacidade,
                conectividade = :conectividade,
                preco = :preco,
                processador = :processador,
                memoria = :memoria,
                camera = :camera,
                sistema_operacional = :sistema_operacional,
                cor = :cor,
                estoque_minimo = :estoque_minimo
            WHERE id_eletronico = :id AND ativo = 1
        ";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':id'                  => $id,
            ':nome'                => $dados['nome'],
            ':descricao'           => $dados['descricao'] ?? null,
            ':numero_lote'         => $dados['numero_lote'] ?? null,
            ':fabricante'          => $dados['fabricante'] ?? null,
            ':categoria'           => $dados['categoria'] ?? null,
            ':tensao'              => $dados['tensao'] ?? null,
            ':dimensoes'           => $dados['dimensoes'] ?? null,
            ':resolucao'           => $dados['resolucao'] ?? null,
            ':capacidade'          => $dados['capacidade'] ?? null,
            ':conectividade'       => $dados['conectividade'] ?? null,
            ':preco'               => $dados['preco'],
            ':processador'         => $dados['processador'] ?? null,
            ':memoria'             => $dados['memoria'] ?? null,
            ':camera'              => $dados['camera'] ?? null,
            ':sistema_operacional' => $dados['sistema_operacional'] ?? null,
            ':cor'                 => $dados['cor'] ?? null,
            ':estoque_minimo'      => $dados['estoque_minimo'] ?? 0
        ]);
    }

    /**
     * Realiza a exclusão lógica do eletrônico (define ativo = 0).
     * Preserva o histórico de movimentações na base de dados.
     *
     * @param int $id
     * @return bool
     */
    public static function desativar(int $id): bool
    {
        $pdo = Database::getInstance();

        $sql = "UPDATE eletronicos SET ativo = 0 WHERE id_eletronico = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * CT11: Obtém os indicadores consolidados (métricas) para o dashboard/resumo do estoque.
     *
     * @return array
     */
    public static function obterResumo(): array
    {
        $pdo = Database::getInstance();

        $sql = "
            SELECT 
                COUNT(e.id_eletronico) AS total_produtos_ativos,
                COALESCE(SUM(est.quantidade_atual), 0) AS total_itens_estoque,
                COALESCE(SUM(CASE WHEN est.quantidade_atual <= e.estoque_minimo THEN 1 ELSE 0 END), 0) AS total_estoque_baixo,
                COALESCE(SUM(est.quantidade_atual * e.preco), 0) AS valor_total_inventario
            FROM eletronicos e
            LEFT JOIN estoque est ON e.id_eletronico = est.id_eletronico
            WHERE e.ativo = 1
        ";

        $stmt = $pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}