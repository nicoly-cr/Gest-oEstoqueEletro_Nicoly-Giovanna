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


======================================================================================================
## [28/10/2026] GET /movimentacoes — EstoqueController::historico() [CT09]
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: Mapear a rota de histórico geral de movimentações de estoque integrando as tabelas `movimentacoes` e `eletronicos`.  
Nossa tentativa: Incluir o `case '/movimentacoes'` no roteador `public/index.php` e acionar o método `historico()` da controller sem filtros.  
Prompt enviado à IA: "Como preencher o modelo do CT09 para o relatório de testes da API?"  
Sugestão da IA: Explicar a consulta `SELECT` com `INNER JOIN` entre `movimentacoes` e `eletronicos` ordenando por `data_movimentacao DESC` e estruturar a tabela formal do CT09.  
Decisão da dupla: Mapear a rota e submeter a requisição `GET /movimentacoes` no Postman.  
Teste realizado: Requisição `GET http://localhost/.../api_eletronicos/movimentacoes` no Postman.  
Resultado obtido: Status `200 OK` com `"total": 6` listando todas as movimentações gravadas por ordem cronológica decrescente.  
O que aprendemos: Como relacionar tabelas usando `INNER JOIN` no PDO para enriquecer a resposta JSON de auditoria.  
======================================================================================================


======================================================================================================
## [28/10/2026] GET /movimentacoes?id=1 — EstoqueController::historico() [CT10 - Ajuste de Rota]
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: A requisição `GET /movimentacoes?id=1` retornava todas as 6 movimentações (`"total": 6`) ou erro `404 Not Found` devido à divergência de barra no `case` do `switch` no `public/index.php`.  
Nossa tentativa: Alterar a declaração no `index.php` para `case 'movimentacoes':` sem barra, o que acabou desviando a rota para o tratamento padrão.  
Prompt enviado à IA: Imagem do Postman exibindo retorno de todas as movimentações e código HTTP 404, solicitando ajuda para filtrar por ID.  
Sugestão da IA: Explicar o tratamento da variável `$route` no Front Controller (`parse_url`), demonstrar o uso do `Request::query('id')` e orientar a restauração do `case '/movimentacoes':` com a barra inicial.  
Decisão da dupla: Restaurar a rota como `case '/movimentacoes':` no `public/index.php` e garantir a captura do Query Param `id` via `Request::query('id')` no `EstoqueController`.  
Teste realizado: Requisição `GET http://localhost/.../api_eletronicos/movimentacoes?id=1` no Postman.  
Resultado obtido: Status `200 OK` com `"total": 1` trazendo exclusivamente o histórico do "Notebook Vision 14".  
O que aprendemos: A importância da correspondência exata de strings no Front Controller para tratar Query Parameters e direcionar requisições ao Controller correto.  
======================================================================================================


======================================================================================================
## [28/10/2026] DELETE /eletronico?id=1 — EletronicoController::desativar() [CT18]
Integrantes e papéis: Driver: Nicoly | Navigator: Nicoly  
Problema: Executar a desativação de um eletrônico mantendo a integridade referencial com a tabela `movimentacoes` e preservando o histórico de auditoria.  
Nossa tentativa: Enviar a requisição `DELETE /eletronico?id=1` sem corpo JSON no Postman.  
Prompt enviado à IA: Imagem do Postman com o retorno `200 OK` e a mensagem `"Eletrônico desativado com sucesso."`, pedindo auxílio para documentar o CT18.  
Sugestão da IA: Explicar a diferença entre exclusão lógica (`UPDATE ativo = 0`) e exclusão física (`DELETE FROM`), destacando o impacto na restrição de chave estrangeira (`ON DELETE RESTRICT`).  
Decisão da dupla: Aplicar a exclusão lógica na Model `Eletronico`, garantindo que o produto `id=1` continue na base de dados para auditoria, mas indisponível para novas movimentações.  
Teste realizado: Requisição `DELETE http://localhost/.../api_eletronicos/eletronico?id=1` no Postman.  
Resultado obtido: Status `200 OK` com JSON `{"erro": false, "mensagem": "Eletrônico desativado com sucesso."}` e coluna `ativo = 0` gravada no MySQL.  
O que aprendemos: Como a exclusão lógica garante a integridade referencial do banco relacional e preserva o histórico de auditoria exigido por normas de negócio.  
======================================================================================================