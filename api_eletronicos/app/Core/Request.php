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
     * Captura um parâmetro da Query String lendo direto do $_GET ou da REQUEST_URI.
     */
    public static function query(?string $key = null, $default = null)
    {
        // 1. Tenta capturar direto do $_GET
        if ($key !== null && isset($_GET[$key]) && $_GET[$key] !== '') {
            return $_GET[$key];
        }

        // 2. Extrai a query string manualmente da URL enviada
        $queryString = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        $params = [];
        if ($queryString) {
            parse_str($queryString, $params);
        }

        if ($key === null) {
            return array_merge($_GET, $params);
        }

        return $params[$key] ?? $default;
    }

    /**
     * Captura a URI/caminho da requisição isolando a rota principal da API.
     */
    public static function uri(): string
    {
        // 1. Obtém o caminho da requisição descartando parâmetros GET (?id=1)
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // 2. Extrai o caminho base do projeto removendo o sufixo public/index.php
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('/public/index.php', '', $scriptName);
        $basePath = str_replace('/index.php', '', $basePath);

        // 3. Subtrai a pasta base do caminho completo
        if (!empty($basePath) && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        // 4. Garante que retorne a rota normalizada (ex: '/eletronico')
        $uri = '/' . ltrim($uri, '/');
        return rtrim($uri, '/') ?: '/';
    }
}

?>