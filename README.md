<p align="center"><img src ="https://theme.zdassets.com/theme_assets/494154/baff07fc755fee5daf2e4a0f42b4552cad1ed68e.png" width="30%" height="30%" /></p>

##
# Vindi - Magento 2

[![Licença do Software][badge-license]](LICENSE)
[![Última Versão no GitHub][badge-versionGitHub]][link-GitHub-release]
[![GitHub commits desde a última Versão][badge-versionGitHub-commits]][link-GitHub-release]

# Descrição
A integração do módulo da Vindi permite criação e gestão de planos e assinaturas através do Magento 2 de forma transparente.

# Requisitos
- Magento 2.3.6+
- cURL habilitado para o PHP
- Certificado SSL
- Conta ativa na [Vindi](https://www.vindi.com.br "Vindi")

# Instalação
É possível realizar a instalação do módulo da Vindi para Magento 2 via [.zip](https://github.com/vindi/vindi-magento2/archive/master.zip), via [Git](https://github.com) ou via [Composer](https://getcomposer.org).

#### Via [composer](https://getcomposer.org)
- Vá até o diretório raíz do Magento e adicione o módulo
> `composer require vindi/vindi-magento2`
- Atualize os módulos disponíveis do Magento
> `bin/magento setup:upgrade`
- O módulo **Vindi_Payment** deverá ser exibido na lista de módulos do Magento
> `bin/magento module:status`

#### Via [git](https://github.com)
- Vá até o diretório raíz do Magento e adicione o módulo
> `git clone https://github.com/vindi/vindi-magento2.git app/code/Vindi/Payment/`
- Atualize os módulos disponíveis do Magento
> `bin/magento setup:upgrade`
- O módulo **Vindi_Payment** deverá ser exibido na lista de módulos do Magento
> `bin/magento module:status`

#### Via [.zip](https://github.com/vindi/vindi-magento2/archive/master.zip)
- Crie a(s) seguinte(s) pasta(s) dentro da pasta **app** do Magento
> `code/Vindi/Payment`
- Faça o download do [.zip](https://github.com/vindi/vindi-magento2/archive/master.zip)
- O caminho deve ser **app/code/Vindi/Payment**
- Extraia os arquivos do **.zip** na pasta **Payment**
- No diretório raíz, atualize os módulos disponíveis do Magento
> `bin/magento setup:upgrade`
- O módulo **Vindi_Payment** deverá ser exibido na lista de módulos do Magento
> `bin/magento module:status`

# Configuração
1. Configurando sua conta Vindi
    - No painel de Administração do Magento, acesse *Vindi -> Configuração*
    - Selecione o modo de operação e informe a chave da API de sua conta Vindi
    - Você deve copiar o link de configuração dos Webhooks, para inseri-lo na plataforma da Vindi
    - Acesse [Vindi](https://app.vindi.com.br) -> Configurações -> Webhooks -> Novo webhook -> e cole o link no campo de URL
    - Você pode clicar na opção **Testar** para validar a configuração
    - Para finalizar a configuração, basta clicar na opção "Criar Webhook"
1. Habilitando/Configurando os métodos de pagamento
    - Em *Lojas -> Vendas -> Métodos de pagamento*, configure e habilite o método de pagamento **Vindi - Cartão de Crédito**

## Novidades na Versão 2.x

A versão 2.x do módulo Vindi para Magento 2 apresenta melhorias significativas e mudanças em relação à versão anterior 1.x. 
Por favor, revise estas mudanças com atenção antes de realizar a atualização da versão 1.x para a versão 2.x:

### 1. Gestão Independente de Planos

- **Novo na 2.x:** Agora, os planos de assinatura podem ser gerenciados de forma independente dos produtos. Após configurar os planos, você pode associá-los a produtos específicos. Isso representa uma mudança significativa em relação à versão 1.x, onde cada produto só podia estar vinculado a um único plano, e o produto precisava ser do tipo "bundle".
- **Impacto da Atualização:** As assinaturas existentes criadas na versão 1.x **não** serão atualizadas no Magento após a atualização para a versão 2.x. Os lojistas precisarão gerenciar essas assinaturas manualmente, se necessário.

### 2. Gestão de Assinaturas pelo Cliente

- **Novo na 2.x:** Os clientes agora podem gerenciar suas assinaturas diretamente na área da conta do cliente na sua loja Magento. No entanto, esta funcionalidade está disponível apenas para novas assinaturas criadas com a versão 2.x.
- **Impacto da Atualização:** As assinaturas criadas com a versão 1.x **não** terão seus perfis de pagamento salvos na área da conta do cliente após a atualização. Apenas novas assinaturas se beneficiarão dessa funcionalidade.

### 3. Geração de Pedidos por Ciclo

- **Novo na 2.x:** O módulo agora gera um novo pedido para cada ciclo de assinatura, permitindo a logística individual de cada item e a gestão de pedidos. Isso substitui o comportamento anterior da versão 1.x, onde apenas faturas eram geradas para cada ciclo.
- **Impacto da Atualização:** O comportamento antigo de gerar apenas faturas para os ciclos de assinatura foi completamente removido na versão 2.x. Os lojistas precisarão ajustar seu fluxo de trabalho para se adaptar ao novo processo de geração de pedidos.

## Dúvidas
Caso necessite de informações sobre a plataforma ou a API, por favor, siga através do canal [Atendimento Vindi](http://atendimento.vindi.com.br/hc/pt-br)

## Contribuindo
Por favor, leia o arquivo [CONTRIBUTING.md](CONTRIBUTING.md).

Caso tenha alguma sugestão ou bug para reportar, por favor, nos comunique através das [issues](./issues).

## Changelog
Tipos de mudanças
- **Adicionado** para novos recursos
- **Ajustado** para mudanças em recursos existentes
- **Depreciado** para recursos que serão removidos em breve
- **Removido** para recursos removidos
- **Corrigido** para correção de falhas
- **Segurança** em caso de vulnerabilidades

Todas as informações sobre cada release podem ser encontradas em [CHANGELOG.md](CHANGELOG.md).

## Créditos
- [Vindi](https://github.com/vindi)
- [Todos os Contribuidores](https://github.com/vindi/vindi-magento2/contributors)

## Licença
GNU GPLv3. Por favor, veja o [Arquivo de Licença](LICENSE) para mais informações.

[badge-license]: https://img.shields.io/badge/license-GPLv3-blue.svg
[badge-versionGitHub]: https://img.shields.io/github/release/vindi/vindi-magento2.svg
[badge-versionGitHub-commits]:  https://img.shields.io/github/commits-since/vindi/vindi-magento2/latest.svg


[link-GitHub-release]: https://github.com/vindi/vindi-magento2/releases
