<?php

// 1. Carregar configurações globais do banco e do projeto
require_once __DIR__ . '/../config/config.php';

// 2. Autoloader PSR-4 para mapeamento do namespace App\
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Request;
use App\Views\JsonView;
use App\Controllers\EletronicoController;
use App\Controllers\EstoqueController;

// 3. Captura do Método HTTP e da URI sanitizada
$method = Request::method();
$uri = Request::uri();

// 4. Roteamento Centralizado (Front Controller)
switch ($uri) {
    // Rotas de Eletrônicos (1, 2, 3, 4)
    case '/eletronico':
        $controller = new EletronicoController();

        if ($method === 'GET') {
            // 1. GET /eletronico — Listar eletrônicos e saldos
            $controller->listar();
        } elseif ($method === 'POST') {
            // 2. POST /eletronico — Cadastrar eletrônico e estoque inicial
            $controller->cadastrar();
        } elseif ($method === 'PUT') {
            // 3. PUT /eletronico — Atualizar dados do eletrônico
            $controller->atualizar();
        } elseif ($method === 'DELETE') {
            // 4. DELETE /eletronico?id=1 — Desativar (exclusão lógica)
            $controller->desativar();
        } else {
            JsonView::render(['erro' => true, 'mensagem' => 'Método HTTP não permitido para esta rota.'], 405);
        }
        break;

    // Rota de Estoque (5)
    case '/estoque':
        $controller = new EstoqueController();

        if ($method === 'PUT') {
            // 5. PUT /estoque — Movimentar estoque: entrada ou saída
            $controller->movimentar();
        } else {
            JsonView::render(['erro' => true, 'mensagem' => 'Método HTTP não permitido para esta rota.'], 405);
        }
        break;

    // Rota de Movimentações / Histórico (6)
    case '/movimentacoes':
        $controller = new EstoqueController(); // ou MovimentacaoController caso optem por separar

        if ($method === 'GET') {
            // 6. GET /movimentacoes — Consultar histórico de movimentações
            $controller->historico();
        } else {
            JsonView::render(['erro' => true, 'mensagem' => 'Método HTTP não permitido para esta rota.'], 405);
        }
        break;

    // Rota 3: GET /eletronicoId?id={ID}
    case '/eletronicoId':
        if ($method === 'GET') {
            $controller = new EletronicoController();
            $controller->buscarPorId();
        } else {
            JsonView::render(['erro' => true, 'mensagem' => 'Método não permitido.'], 405);
        }
        break;

    // Rota 4: GET /eletronicoNome?nome={termo}
    case '/eletronicoNome':
        $controller = new EletronicoController();
        if ($method === 'GET') {
            $controller->buscarPorNome();
        } else {
            JsonView::render(['erro' => true, 'mensagem' => 'Método HTTP não permitido para esta rota.'], 405);
        }
        break;

    
        
    // Rota padrão para endpoints não mapeados
    default:
        JsonView::render(['erro' => true, 'mensagem' => 'Rota não encontrada.'], 404);
        break;
}

?>