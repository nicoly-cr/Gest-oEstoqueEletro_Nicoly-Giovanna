<?php

namespace App\Core;

class Request 
{
    /**
     * Captura o corpo da requisição JSON e retorna como array associativo.
     * Permite buscar por uma chave específica ou retornar todos os dados.
     */
    public static function input(?string $key = null, $default = null)
    {
        $rawContent = file_get_contents('php://input');
        $data = json_decode($rawContent, true) ?? [];

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Captura o método HTTP da requisição (GET, POST, PUT, DELETE).
     */
    public static function method(): string 
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Captura um parâmetro da Query String ($_GET).
     * Se nenhuma chave for informada, retorna todo o array $_GET sanitizado.
     */
    public static function query(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    /**
     * Captura a URI/caminho da requisição limpando a Query String.
     */
    public static function uri(): string
    {
        // 1. Extrai apenas o caminho da URL limpando query strings (?id=1)
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // 2. Identifica o diretório onde o index.php está sendo executado
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');

        // 3. Remove o diretório do script do início do caminho recebido
        if ($scriptDir !== '/' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        // 4. Garante o retorno sanitizado da rota com a barra inicial (ex: '/eletronico')
        return '/' . ltrim($uri, '/');
    }
}

?>