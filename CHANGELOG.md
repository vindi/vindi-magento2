# Notas das versões

## [2.2.0 - 14/01/2025](https://github.com/vindi/vindi-magento2/releases/tag/2.2.0)

- Novo layout do cartão de crédito na página de checkout

## [2.1.0 - 30/10/2024](https://github.com/vindi/vindi-magento2/releases/tag/2.1.0)

- Adiciona a possibilidade de edição de valor de itens em uma assinatura
- Adiciona a funcionalidade de link de pagamento
- Adiciona a funcionalidade de envio de e-mail em massa no link de pagamento
- Remove classes deprecadas pelo Magento

## [2.0.1 - 26/09/2024](https://github.com/vindi/vindi-magento2/releases/tag/2.0.1)

- Corrige erro "The attribute set ID is incorrect. Verify the ID and try again" durante a etapa de atualização do novo módulo de recorrência.

## [2.0.0 - 03/09/2024](https://github.com/vindi/vindi-magento2/releases/tag/2.0.0)

**Breaking changes**

- Adiciona suporte para Magento 2.4+
- Adiciona suporte à PHP 8
- Adiciona gestão de planos no painel administrativo
- Adiciona gestão de assinaturas no painel administrativo
- Adiciona gestão de assinaturas na central do cliente
- Adiciona possibilidade de alterar método de pagamento de assinaturas
- Adiciona possibilidade de ter vários planos por produto
- Ajustado configurações no painel, unificando-as
- Adiciona log de apis no painel
- Adiciona informação do pagamento ne tela de pedido
- Adiciona dados de assinatura na tela de pedido
- Adiciona cron para limpeza de logs antigos
- Adiciona comando no bin/magento para rodar cron manualmente, se necessário
- Adiciona dados da assinatura no additional_information do item do pedido

## [1.5.0 - 07/05/2024](https://github.com/vindi/vindi-magento2/releases/tag/1.5.0)

- Insere método de pagamento Bolepix

## [1.4.0 - 15/02/2024](https://github.com/vindi/vindi-magento2/releases/tag/1.4.0)

- Insere método de pagamento Pix
- Adiciona filtro nas assinaturas pelo método de pagamento Pix
- Correção no webhook para pedidos avulsos

## [1.3.0 - 10/06/2020](https://github.com/vindi/vindi-magento2/releases/tag/1.3.0)

- Insere produto do tipo plano Vindi para criação de assinaturas
- Insere criação de assinaturas via checkout
- Insere função para cadastro e consulta de clientes
- Insere função para cadastro e consulta de perfis de pagamento
- Insere função para cadastro e consulta de produtos
- Insere função de envio de fretes e taxas
- Insere suporte a webhooks de renovação de assinatura
- Insere renovação de pedidos
- Insere página para consulta e edição de assinaturas no painel administrativo

## [1.2.0 - 19/08/2019](https://github.com/vindi/vindi-magento2/releases/tag/1.2.0)

- Corrige falha ao salvar as configurações de métodos de pagamento sem informar a chave API
- Adiciona atribuicão de status padrao após confirmação de pagamento dos pedidos
- Ajusta envio de descontos, fretes e taxas para a plataforma Vindi
- Ajusta fluxo de compra em transações com suspeita de fraude

## [1.1.0 - 15/05/2019](https://github.com/vindi/vindi-magento2/releases/tag/1.1.0)

- Insere método de pagamento Boleto Bancário

## [1.0.0 - 14/08/2018](https://github.com/vindi/vindi-magento2/releases/tag/1.0.0)

- Versão Inicial
- Disponibilização Marketplace
