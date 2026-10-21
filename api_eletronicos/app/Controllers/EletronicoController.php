<?php

namespace App\Controllers;

use App\Models\Eletronico;
use App\Views\JsonView;

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
}

?>