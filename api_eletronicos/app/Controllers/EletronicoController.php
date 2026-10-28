<?php

namespace App\Controllers;

use App\Core\Request;
use App\Models\Eletronico;
use App\Views\JsonView;
use Exception;

class EletronicoController 
{
    /**
     * Rota 1: GET /eletronico — Listar eletrônicos e saldos
     */
    public function listar(): void
    {
        $eletronicos = Eletronico::listar();
        
        JsonView::render([
            'erro'  => false,
            'total' => count($eletronicos),
            'dados' => $eletronicos
        ], 200);
    }

    /**
     * Rota 2: POST /eletronico — Cadastrar eletrônico e estoque inicial
     */
    public function cadastrar(): void
    {
        $dados = Request::input();

        // Validação básica dos campos obrigatórios
        if (
            empty($dados['nome']) || 
            empty($dados['numero_lote']) || 
            empty($dados['fabricante']) || 
            empty($dados['categoria'])
        ) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'Preencha todos os campos obrigatórios (nome, numero_lote, fabricante, categoria).'
            ], 400);
        }

        try {
            $idEletronico = Eletronico::cadastrar($dados);

            JsonView::render([
                'erro'          => false,
                'mensagem'      => 'Eletrônico e estoque inicial cadastrados com sucesso.',
                'id_eletronico' => $idEletronico
            ], 201);

        } catch (Exception $e) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'Erro ao cadastrar eletrônico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rota 3: GET /eletronicoId?id={ID} — Buscar eletrônico específico por ID
     */
    public function buscarPorId(): void
    {
        // Captura o parâmetro 'id' passado na Query String (ex: ?id=1)
        $id = Request::query('id');

        // Valida se o ID foi informado e se é um valor numérico válido
        if (!$id || !is_numeric($id)) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'O parâmetro "id" é obrigatório e deve ser um número válido.'
            ], 400);
        }

        $eletronico = Eletronico::buscarPorId((int) $id);

        // Se não encontrar o eletrônico ativo com o ID informado
        if (!$eletronico) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'Eletrônico não encontrado.'
            ], 404);
        }

        // Retorna o eletrônico encontrado com sucesso
        JsonView::render([
            'erro'  => false,
            'dados' => $eletronico
        ], 200);
    }

    /**
     * Rota 4: GET /eletronicoNome?nome={termo} — Buscar eletrônicos por nome
     */
    public function buscarPorNome(): void
    {
        // Captura o parâmetro 'nome' da Query String (ex: ?nome=Monitor)
        $nome = Request::query('nome');

        // Valida se o termo de busca foi informado
        if (empty($nome) || trim($nome) === '') {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'O parâmetro "nome" é obrigatório para realizar a busca.'
            ], 400);
        }

        $eletronicos = Eletronico::buscarPorNome(trim($nome));

        // Retorna a lista encontrada (mesmo que seja array vazio [])
        JsonView::render([
            'erro'  => false,
            'total' => count($eletronicos),
            'dados' => $eletronicos
        ], 200);
    }

    /**
     * Rota 5: PUT /eletronico — Atualizar dados de um eletrônico
     */
    public function atualizar(): void
    {
        $dados = Request::input();

        // Valida se o ID foi informado no corpo do JSON
        if (empty($dados['id_eletronico']) || !is_numeric($dados['id_eletronico'])) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'O campo "id_eletronico" é obrigatório no corpo da requisição.'
            ], 400);
        }

        // Validações básicas de campos obrigatórios
        if (
            empty($dados['nome']) || 
            empty($dados['numero_lote']) || 
            empty($dados['fabricante']) || 
            empty($dados['categoria'])
        ) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'Preencha os campos obrigatórios (nome, numero_lote, fabricante, categoria).'
            ], 400);
        }

        $sucesso = Eletronico::atualizar($dados);

        if (!$sucesso) {
            JsonView::render([
                'erro'     => true,
                'mensagem' => 'Eletrônico não encontrado ou nenhuma alteração foi realizada.'
            ], 404);
        }

        JsonView::render([
            'erro'     => false,
            'mensagem' => 'Dados do eletrônico atualizados com sucesso.'
        ], 200);
    }

    /**
     * Rota 6: DELETE /eletronico — Desativa dados de um eletrônico
     */
    public function desativar(): void
    {
        // Captura o 'id' da Query String (?id=1)
        $id = Request::query('id');

        if (!$id || !is_numeric($id)) {
            JsonView::render([
                'erro' => true,
                'mensagem' => 'Informe o ID do eletrônico a ser desativado.'
            ], 400);
            return;
        }

        try {
            Eletronico::desativar((int)$id);

            JsonView::render([
                'erro' => false,
                'mensagem' => 'Eletrônico desativado com sucesso.'
            ], 200);
        } catch (Exception $e) {
            JsonView::render([
                'erro' => true,
                'mensagem' => $e->getMessage()
            ], 400);
        }
    }
}

?>