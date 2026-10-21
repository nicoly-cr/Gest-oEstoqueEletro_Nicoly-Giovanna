<?php

namespace App\Views;

class JsonView
{
    /**
     * Renderiza uma resposta em formato JSON para o cliente da API REST.
     *
     * @param array $payload Dados a serem convertidos para JSON
     * @param int $statusCode Código de estado HTTP (padrão 200)
     */
    public static function render(array $payload, int $statusCode = 200): void
    {
        // 1. Limpa qualquer saída anterior no buffer para evitar corrupção do JSON
        if (ob_get_length()) {
            ob_clean();
        }

        // 2. Define o código de resposta HTTP (200, 201, 400, 404, 422, 500, etc.)
        http_response_code($statusCode);

        // 3. Define o cabeçalho informando que o conteúdo é JSON com codificação UTF-8
        header('Content-Type: application/json; charset=utf-8');

        // Cabeçalhos opcionais úteis para evitar cache no cliente durante testes
        header('Cache-Control: no-cache, must-revalidate');

        // 4. Converte o array $payload para a string JSON e imprime na tela
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 5. Encerra a execução do script garantindo que nenhum HTML ou aviso seja anexado
        exit;
    }
}

?>