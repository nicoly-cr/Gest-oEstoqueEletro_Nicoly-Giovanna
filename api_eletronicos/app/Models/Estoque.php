<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Estoque 
{
    /**
     * Realiza a movimentação de estoque (ENTRADA ou SAIDA) e grava a auditoria.
     *
     * @param array $dados
     * @return array
     */
    public static function movimentar(array $dados): array
    {
        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            $idEletronico = (int) $dados['id_eletronico'];
            $tipo = strtoupper($dados['tipo']);
            $quantidade = (int) $dados['quantidade'];
            $observacao = $dados['observacao'] ?? 'Movimentação via API';

            // 1. Busca o saldo atual e bloqueia a linha com FOR UPDATE
            $sqlSaldo = "SELECT quantidade_atual FROM estoque WHERE id_eletronico = :id FOR UPDATE";
            $stmtSaldo = $pdo->prepare($sqlSaldo);
            $stmtSaldo->execute([':id' => $idEletronico]);
            $estoqueAtual = $stmtSaldo->fetch(PDO::FETCH_ASSOC);

            if (!$estoqueAtual) {
                throw new Exception("Registro de estoque não encontrado para o eletrônico informado.");
            }

            $saldoAnterior = (int) $estoqueAtual['quantidade_atual'];

            // 2. Calcula o novo saldo e valida regra de negócio (não permitir saldo negativo)
            if ($tipo === 'ENTRADA') {
                $saldoPosterior = $saldoAnterior + $quantidade;
            } elseif ($tipo === 'SAIDA') {
                if ($saldoAnterior < $quantidade) {
                    throw new Exception("Saldo insuficiente em estoque. Saldo atual: {$saldoAnterior}, Tentativa de saída: {$quantidade}.");
                }
                $saldoPosterior = $saldoAnterior - $quantidade;
            } else {
                throw new Exception("Tipo de movimentação inválido. Utilize 'ENTRADA' ou 'SAIDA'.");
            }

            // 3. Atualiza a tabela estoque
            $sqlUp = "UPDATE estoque SET quantidade_atual = :novo_saldo WHERE id_eletronico = :id";
            $stmtUp = $pdo->prepare($sqlUp);
            $stmtUp->execute([
                ':novo_saldo' => $saldoPosterior,
                ':id'         => $idEletronico
            ]);

            // 4. Registra na tabela movimentacoes
            // id_usuario = 1 fixo por não haver autenticação no escopo
            $sqlMov = "
                INSERT INTO movimentacoes (
                    id_eletronico, id_usuario, tipo, quantidade, 
                    saldo_anterior, saldo_posterior, observacao
                ) VALUES (
                    :id_eletronico, 1, :tipo, :quantidade, 
                    :saldo_anterior, :saldo_posterior, :observacao
                )
            ";
            $stmtMov = $pdo->prepare($sqlMov);
            $stmtMov->execute([
                ':id_eletronico'   => $idEletronico,
                ':tipo'            => $tipo,
                ':quantidade'      => $quantidade,
                ':saldo_anterior'  => $saldoAnterior,
                ':saldo_posterior' => $saldoPosterior,
                ':observacao'      => $observacao
            ]);

            $pdo->commit();

            return [
                'id_eletronico'   => $idEletronico,
                'tipo'            => $tipo,
                'quantidade'      => $quantidade,
                'saldo_anterior'  => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior
            ];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Retorna o histórico de movimentações geral ou filtrado por produto.
     *
     * @param int|null $idEletronico
     * @return array
     */
    public static function historico(?int $idEletronico = null): array
    {
        $pdo = Database::getInstance();

        $sql = "
            SELECT 
                m.id_movimentacao,
                m.id_eletronico,
                e.nome AS eletronico_nome,
                m.tipo,
                m.quantidade,
                m.saldo_anterior,
                m.saldo_posterior,
                m.observacao,
                m.data_movimentacao
            FROM movimentacoes m
            INNER JOIN eletronicos e ON m.id_eletronico = e.id_eletronico
        ";

        $params = [];

        // Se o parâmetro de filtro por ID foi fornecido (CT10)
        if ($idEletronico !== null) {
            $sql .= " WHERE m.id_eletronico = :id_eletronico";
            $params[':id_eletronico'] = $idEletronico;
        }

        $sql .= " ORDER BY m.data_movimentacao DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>