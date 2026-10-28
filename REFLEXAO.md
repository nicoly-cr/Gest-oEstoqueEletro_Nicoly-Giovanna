# Registro de Decisões, Aprendizado e Interações com a IA

**Projeto:** Gestão de Estoque de Eletrônicos — API REST Back-end PHP 8.1+ (POO, MVC, PDO)  
**Integrantes:** Nicoly & Giovanna  
**Turma:** 2IDS-A-SRPQ

---

## 📌 Visão Geral do Documento
Este documento registra o histórico das interações com o Tutor Socrático de IA durante o desenvolvimento e teste da API REST da Fase 3. Cada entrada documenta o problema enfrentado, as análises de causa-raiz, as hipóteses diagnósticas, as decisões tomadas pela dupla, as alterações no código/ambiente e as evidências de teste obtidas no Postman e no banco de dados MySQL.

---


=============================================== MODELO ===============================================
## [Data] — [HTTP] [rota]
Integrantes e papéis: Driver: ... | Navigator: ...
Problema: 
Nossa tentativa: ...
Prompt enviado à IA: “...”
Sugestão da IA: ...
Decisão da dupla: aceitar / adaptar / rejeitar — porque ...
Teste realizado: ...
Resultado obtido: HTTP ... | JSON ...
Evidência: captura/arquivo/commit ...
O que aprendemos: ...
======================================================================================================


======================================================================================================
## [21/10/2026] GET /eletronico — EletronicoController::listar()
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: Definir a rota de listagem geral de produtos e retornar os saldos atrelados via `JsonView`.  
Nossa tentativa: Mapear a case `/eletronico` no `public/index.php` e acionar o método `listar()` da controller.  
Prompt enviado à IA: "Como testar a listagem de eletrônicos ativos e seus respectivos saldos no Postman?"  
Sugestão da IA: Verificar o roteamento no `public/index.php`, garantir o uso do método `EletronicoController::listar()` e validar a resposta via `JsonView`.  
Decisão da dupla: Executar a chamada `GET /eletronico` no Postman e validar a integração da Model `Eletronico` com a tabela `estoque`.  
Teste realizado: Requisição `GET http://localhost/.../api_eletronicos/eletronico` no Postman.  
Resultado obtido: Status `200 OK` com JSON contendo `erro: false` e array de produtos com saldos.  
O que aprendemos: Como relacionar a rota no Front Controller com a Model e formatar saídas em JSON limpas.  
======================================================================================================


======================================================================================================
## [21/10/2026] POST /eletronico — EletronicoController::cadastrar()
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: O endpoint retornou erro HTTP 500 mesmo informando os dados no Body do Postman.  
Nossa tentativa: Enviar JSON no Body com os campos cadastrais do produto.  
Prompt enviado à IA: Imagem do Postman com o Body JSON preenchido e retorno HTTP 500 informando ausência de campos obrigatórios.  
Sugestão da IA: Verificar se o cabeçalho `Content-Type: application/json` estava ativado na aba Headers e ajustar lote duplicado para `LOTE-MON-003`.  
Decisão da dupla: Corrigir os cabeçalhos no Postman e alterar o número de lote para garantir unicidade.  
Teste realizado: Requisição `POST http://localhost/.../api_eletronicos/eletronico` no Postman.  
Resultado obtido: Status `201 Created` retornando `{"erro": false, "mensagem": "Eletrônico e estoque inicial cadastrados com sucesso.", "id_eletronico": 9}`.  
O que aprendemos: A importância da leitura correta da stream `php://input` e da configuração de cabeçalhos HTTP do cliente.  
======================================================================================================


======================================================================================================
## [27/10/2026] GET /eletronicoId — EletronicoController::buscarPorId()
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: O teste retornou erro fatal `Call to undefined method App\Controllers\EletronicoController::buscarPorId()`.  
Nossa tentativa: Enviar requisição `GET /eletronicoId?id=1` após adicionar o roteamento no `index.php`.  
Prompt enviado à IA: Imagem do Postman exibindo o erro fatal de método não existente na controller.  
Sugestão da IA: Orientar a implementação da assinatura `public function buscarPorId(): void` em `EletronicoController.php` lendo o parâmetro `$_GET['id']`.  
Decisão da dupla: Implementar o método `buscarPorId()` validando se o ID é numérico e retornando o registro via `JsonView`.  
Teste realizado: Requisição `GET http://localhost/.../api_eletronicos/eletronicoId?id=1` no Postman.  
Resultado obtido: Status `200 OK` trazendo os dados completos do "Notebook Vision 14" e seu saldo de estoque `8`.  
O que aprendemos: Como capturar e validar Query Parameters da requisição HTTP e integrar com a Model.  
======================================================================================================


======================================================================================================
## [27/10/2026] GET /eletronicoNome — EletronicoController::buscarPorNome()
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: A busca parcial por nome retornou HTTP 404 Not Found devido à falta do case no roteador.  
Nossa tentativa: Submeter `GET /eletronicoNome?nome=Monitor` no Postman.  
Prompt enviado à IA: Imagem do Postman enviando `GET /eletronicoNome?nome=Monitor` e recebendo resposta HTTP 404.  
Sugestão da IA: Explicar a lógica do operador SQL `LIKE` com wildcards `%` no PDO e orientar a inclusão da rota no `public/index.php`.  
Decisão da dupla: Adicionar `case '/eletronicoNome'` no Front Controller e implementar a consulta com Prepared Statement (`:nome => '%' . $nome . '%'`).  
Teste realizado: Requisição `GET http://localhost/.../api_eletronicos/eletronicoNome?nome=Monitor` no Postman.  
Resultado obtido: Status `200 OK` com `"total": 2` listando os monitores ativos cadastrados.  
O que aprendemos: Como montar buscas parciais textuais seguras utilizando PDO para evitar SQL Injection.  
======================================================================================================


======================================================================================================
## [27/10/2026] PUT /eletronico — EletronicoController::atualizar()
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: A tentativa de atualizar o produto disparou a listagem geral porque foi enviado com o verbo `GET`.  
Nossa tentativa: Preencher os dados no Body e acionar o endpoint `/eletronico`.  
Prompt enviado à IA: Imagem do Postman exibindo o Body preenchido com dados do produto id=9, porém com o verbo HTTP GET selecionado.  
Sugestão da IA: Explicar a semântica do método `PUT` no padrão REST e instruir a mudança do seletor no Postman para `PUT`.  
Decisão da dupla: Alterar o seletor do método HTTP no Postman para `PUT` e reexecutar a requisição.  
Teste realizado: Requisição `PUT http://localhost/.../api_eletronicos/eletronico` com JSON no Body.  
Resultado obtido: Status `200 OK` com mensagem `"Dados do eletrônico atualizados com sucesso."`.  
O que aprendemos: A relevância dos verbos HTTP corretos no padrão REST para o roteamento adequado da aplicação.  
======================================================================================================


======================================================================================================
## [28/10/2026] PUT /estoque — EstoqueController::movimentar()
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: Erro fatal do PHP `Class "App\Controllers\EstoqueController" not found` no `index.php`.  
Nossa tentativa: Submeter a chamada `PUT /estoque` com payload de movimentação de entrada.  
Prompt enviado à IA: Imagem do Postman com o erro fatal de classe `EstoqueController` não encontrada.  
Sugestão da IA: Explicar o funcionamento do autoloader PSR-4 (`spl_autoload_register`) e orientar a criação do `EstoqueController.php` e `Estoque.php`.  
Decisão da dupla: Criar o Controller e a Model de Estoque implementando transações PDO (`beginTransaction`, `commit`, `rollBack`) e trava `FOR UPDATE`.  
Teste realizado: Requisição `PUT http://localhost/.../api_eletronicos/estoque` com 5 unidades de entrada.  
Resultado obtido: Status `200 OK` confirmando a movimentação de ENTRADA e atualização do saldo de 10 para 15.  
O que aprendemos: Como configurar namespaces PSR-4 e a importância do controle de transações para integridade de dados.  
======================================================================================================


======================================================================================================
## [28/10/2026] PUT /estoque — Estoque::movimentar() [Saída Válida]
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: Garantir a redução correta do saldo de estoque e o registro do histórico de saída.  
Nossa tentativa: Montar payload JSON com `"tipo": "SAIDA"` e `"quantidade": 2` para o produto de ID 9.  
Prompt enviado à IA: Imagem do Postman executando o teste de saída com 2 unidades no produto id=9.  
Sugestão da IA: Validar a subtração de saldo e solicitar a execução da requisição no Postman.  
Decisão da dupla: Aceitar e submeter a requisição no Postman para conferir a atualização do saldo posterior.  
Teste realizado: Requisição `PUT http://localhost/.../api_eletronicos/estoque` com tipo SAIDA.  
Resultado obtido: Status `200 OK` exibindo o saldo anterior em 13 e o saldo posterior reduzido com precisão para 11.  
O que aprendemos: Como efetuar baixas atômicas de saldo preservando a auditoria no histórico de movimentações.  
======================================================================================================


======================================================================================================
## [28/10/2026] PUT /estoque — Estoque::movimentar() [Saldo Insuficiente]
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: Impedir que uma saída com quantidade maior que o saldo atual torne o estoque negativo ou grave dados corrompidos.  
Nossa tentativa: Enviar um payload com quantidade de saída absurda (`99999`) para o produto ID 9 (saldo 11).  
Prompt enviado à IA: "Como validar a regra que impede saldo negativo e aciona o rollback transacional?"  
Sugestão da IA: Orientar o envio da quantidade maior que o saldo e instruir a verificação do lançamento de exceção e o rollback.  
Decisão da dupla: Lançar uma `Exception` no código e capturá-la no `catch` para acionar o `$pdo->rollBack()` e responder via `JsonView`.  
Teste realizado: Requisição `PUT http://localhost/.../api_eletronicos/estoque` com quantidade 99999 e consulta posterior no MySQL.  
Resultado obtido: Status HTTP `400 Bad Request` com mensagem de saldo insuficiente. O saldo permaneceu intacto em 11 e nada foi gravado no histórico.  
O que aprendemos: Como utilizar Rollback de transações PDO para proteger a aplicação contra inconsistências de dados e saldos negativos.
======================================================================================================



















========================================== MODELO ==========================================
## [Data] — [HTTP] [rota]
Integrantes e papéis: Driver: ... | Navigator: ...
Problema: 
Nossa tentativa: ...
Prompt enviado à IA: “...”
Sugestão da IA: ...
Decisão da dupla: aceitar / adaptar / rejeitar — porque ...
Teste realizado: ...
Resultado obtido: HTTP ... | JSON ...
Evidência: captura/arquivo/commit ...
O que aprendemos: ...


============================================================================================
## [21/10/2026] GET /eletronico — Model Eletronico::listar()   
Integrantes e papéis: Driver: [Nome] | Navigator: [Nome]   
Problema: Definição da consulta SQL com LEFT JOIN e cálculo de estoque_baixo.   
Nossa tentativa: Propor o uso de `LEFT JOIN` para garantir que produtos sem registo na tabela de estoque não fossem ocultados da listagem.
Prompt enviado à IA: Resposta do usuário escolhendo LEFT JOIN.   
Sugestão da IA: Confirmar LEFT JOIN com COALESCE e orientar sobre o cálculo condicional na SQL.   
Decisão da dupla: Aceitar. Escrever o método listar() no Model Eletronico.   
Teste realizado: Análise lógica da consulta no MySQL Workbench.   
Resultado obtido: Aguardando envio do código da dupla para revisão.   
O que aprendemos: A diferença prática do LEFT JOIN para não ocultar produtos e como projetar campos calculados.   