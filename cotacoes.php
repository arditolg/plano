<!DOCTYPE html>
<html>
<head>
    <title>Lista de Cotações</title>
</head>
<body>
    <h1>Lista de Cotações</h1>
    <div id="cotas">
        <!-- Aqui serão adicionadas dinamicamente as cotações -->
    </div>

    <script>
        // Função para carregar os dados do arquivo JSON
        function carregarDadosJson(nomeArquivo) {
            return fetch(nomeArquivo)
                .then(response => response.json())
                .catch(error => {
                    console.error('Erro ao carregar o arquivo JSON:', error);
                });
        }

        // Função para criar os elementos HTML das cotações
        function criarElementoCotacao(cotacao) {
            var elementoCotacao = document.createElement('div');
            elementoCotacao.innerHTML = '<h2>ID da Cotação: ' + cotacao.id_cotacao + '</h2>' +
                                        '<p>Quantidade de Beneficiários: ' + cotacao.quantidadeBeneficiarios + '</p>';

            // Criar lista de beneficiários
            var listaBeneficiarios = document.createElement('ul');
            cotacao.beneficiarios.forEach(beneficiario => {
                var itemBeneficiario = document.createElement('li');
                itemBeneficiario.innerHTML = '<strong>Nome:</strong> ' + beneficiario.nome +
                                             ' | <strong>Idade:</strong> ' + beneficiario.idade +
                                             ' | <strong>Plano:</strong> ' + beneficiario.plano +
                                             ' | <strong>Valor do Plano:</strong> R$ ' + beneficiario.valor_plano.toFixed(2);
                listaBeneficiarios.appendChild(itemBeneficiario);
            });

            elementoCotacao.appendChild(listaBeneficiarios);
            return elementoCotacao;
        }

        // Função para exibir as cotações na página
        function exibirCotacoes(cotacoes) {
            var divCotas = document.getElementById('cotas');

            cotacoes.forEach(cotacao => {
                var elementoCotacao = criarElementoCotacao(cotacao);
                divCotas.appendChild(elementoCotacao);
            });
        }

        // Função para carregar e exibir as cotações
        function carregarEExibirCotacoes() {
            carregarDadosJson('cotacao.json')
                .then(cotacoes => {
                    // Transformar o objeto em um array de cotações
                    const cotacoesArray = Object.values(cotacoes);
                    exibirCotacoes(cotacoesArray);
                });
        }

        // Chamando a função para carregar e exibir as cotações
        carregarEExibirCotacoes();
    </script>
</body>
</html>
