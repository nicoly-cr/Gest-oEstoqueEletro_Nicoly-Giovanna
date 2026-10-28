<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Estoque;
use App\Views\JsonView;
use Exception;

class EstoqueController 
{
    /**
     * Rota 5: PUT /estoque — Movimentar estoque: entrada ou saída
     */
    public function movimentar(): void
    {
        $dados = Request::input();

        // Validação dos campos obrigatórios
        if (
            empty($dados['id_eletronico']) || 
            empty($dados['tipo']) || 
            !isset($dados['quantidade']) || 
            (int)$dados['quantidade'] <= 0
        ) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'Informe "id_eletronico", "tipo" (ENTRADA ou SAIDA) e "quantidade" maior que zero.'
            ], 400);
        }

        try {
            $resultado = Estoque::movimentar($dados);

            JsonView::render([
                'erro'     => false,
                'mensagem' => 'Movimentação de estoque realizada com sucesso.',
                'dados'    => $resultado
            ], 200);

        } catch (Exception $e) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }

    /**
    * Rota 6: GET /movimentacoes — Consultar histórico de movimentações
    */
    public function historico(): void
    {
        $movimentacoes = Estoque::historico();

        JsonView::render([
            'erro'  => false,
            'total' => count($movimentacoes),
            'dados' => $movimentacoes
        ], 200);
    }
}

?>