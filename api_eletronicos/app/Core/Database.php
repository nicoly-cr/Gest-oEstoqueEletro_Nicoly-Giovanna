<?php

namespace App\Core;

use PDO;
use PDOException;

class Database 
{
    // 1. Propriedade estática e privada para armazenar a única instância do PDO
    private static ?PDO $instance = null;

    // 2. Construtor privado: impede a instanciação direta com 'new Database()'
    private function __construct() 
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Em ambiente de API, é ideal tratar o erro ou relançar para a View tratar
            die(json_encode([
                'status'  => 'error',
                'mensagem' => 'Falha na conexão com o banco de dados: ' . $e->getMessage()
            ]));
        }
    }

    // Impede a clonagem da classe
    private function __clone() {}

    // Impede a desserialização da classe
    public function __wakeup() 
    {
        throw new \Exception("Não é permitido desserializar uma instância de Singleton.");
    }

    // 3. Método estático público para recuperar a conexão ativa
    public static function getInstance(): PDO 
    {
        if (self::$instance === null) {
            new self(); // Executa o construtor privado se não houver conexão
        }

        return self::$instance;
    }
}

?>