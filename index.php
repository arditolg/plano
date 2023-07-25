<!DOCTYPE html>
<html>

<head>
    <title>Formulário de Beneficiários</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }

        h1 {
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select {
            cursor: pointer;
        }

        fieldset {
            border: none;
            margin-bottom: 20px;
        }

        .hidden {
            display: none;
        }

        #valor_total_cotacao {
            font-weight: bold;
            font-size: 18px;
            color: #007BFF;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <h1>Formulário de Beneficiários</h1>
    <form action="salvar_cotacao.php" method="post">
        <label for="quantidade">Quantidade de Beneficiários:</label>
        <input type="number" id="quantidade" name="quantidade" min="1" required><br><br>
        <input type="hidden" id="id_cotacao" name="id_cotacao" value="0">

        <fieldset>
            <legend>Informações de cada Beneficiário:</legend>
            <div id="beneficiarios">
                <!-- Aqui serão adicionados dinamicamente os campos de cada beneficiário -->
            </div>
        </fieldset>

        <input type="submit" value="Enviar">
    </form>

    <!-- Div para mostrar o valor total -->
    <div id="valor_total_cotacao">Valor Total da Cotação: R$ 0.00</div>


    <script>
        // Variável global para controlar o id_cotacao
        var idCotacao = 1;

        // Função para obter o próximo id_cotacao
        function obterProximoIdCotacao() {
            return idCotacao++;
        }

        // Função para salvar a cotação no arquivo cotacao.json
        function salvarCotacao(cotacao) {
            // Primeiro, carregue as cotações existentes do arquivo cotacao.json
            carregarDadosJson('cotacao.json')
                .then(cotacoesExistentes => {
                    // Verifique se há cotações existentes
                    if (cotacoesExistentes && cotacoesExistentes.length > 0) {
                        // Encontre o maior id_cotacao existente
                        var maiorIdCotacao = Math.max(...cotacoesExistentes.map(c => c.id_cotacao));
                        // Defina o próximo id_cotacao como maiorIdCotacao + 1
                        idCotacao = maiorIdCotacao + 1;

                        // Adicione a nova cotação à lista de cotacoesExistentes
                        cotacao.id_cotacao = idCotacao;
                        cotacoesExistentes.push(cotacao);

                        // Salve a lista atualizada no arquivo cotacao.json
                        salvarDadosJson('cotacao.json', cotacoesExistentes)
                            .then(() => {
                                console.log('Cotação salva com sucesso!');
                            })
                            .catch(error => {
                                console.error('Erro ao salvar cotação:', error);
                            });
                    } else {
                        // Caso não existam cotações no arquivo, crie uma nova lista com a cotação atual e salve-a no arquivo cotacao.json
                        cotacao.id_cotacao = 1;
                        salvarDadosJson('cotacao.json', [cotacao])
                            .then(() => {
                                console.log('Cotação salva com sucesso!');
                            })
                            .catch(error => {
                                console.error('Erro ao salvar cotação:', error);
                            });
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar o arquivo cotacao.json:', error);
                });
        }


        // Função para carregar os planos e preços dos arquivos JSON
        function carregarDadosJson(nomeArquivo) {
            return fetch(nomeArquivo)
                .then(response => response.json())
                .catch(error => {
                    console.error('Erro ao carregar o arquivo JSON:', error);
                });
        }

        // Função para obter o valor do plano com base na idade do beneficiário
        function obterValorPlano(idade, plano, precos) {
            if (precos[plano] && precos[plano]['minimo_vidas'] === 1) {
                if (idade >= 0 && idade <= 17) {
                    return precos[plano]['faixa1'];
                } else if (idade >= 18 && idade <= 40) {
                    return precos[plano]['faixa2'];
                } else {
                    return precos[plano]['faixa3'];
                }
            }
            return 0; // Caso o plano não esteja presente no arquivo prices.json ou minimo_vidas seja diferente de 1, retorna 0.
        }

        // Função para atualizar o campo valor_plano_ com base na seleção do plano
        function mostrarValorPlano(event) {
            var select = event.target;
            var valorPlanoSpan = select.parentNode.querySelector('[id^="valor_plano_"]');

            if (select.value !== "") {
                valorPlanoSpan.classList.remove('hidden');
            } else {
                valorPlanoSpan.classList.add('hidden');
            }

            // Atualizar o valor do plano somente se o campo valor_plano_ não estiver oculto
            if (!valorPlanoSpan.classList.contains('hidden')) {
                atualizarValorPlano(event);
            }
        }

        // Função para atualizar o valor do plano escolhido
        function atualizarValorPlano(event) {
            var select = event.target;
            var planoEscolhido = select.value;
            var idadeBeneficiario = parseInt(select.parentNode.querySelector('[id^="idade_beneficiario_"]').value);

            // Carrega os preços do arquivo prices.json
            carregarDadosJson('prices.json')
                .then(precos => {
                    // Chama a função para obter o valor do plano com base na idade
                    var valorPlano = obterValorPlano(idadeBeneficiario, planoEscolhido, precos);

                    // Atualiza o elemento <span> com o valor do plano
                    var valorPlanoSpan = select.parentNode.querySelector('[id^="valor_plano_"]');
                    valorPlanoSpan.textContent = 'Valor do plano: R$ ' + valorPlano.toFixed(2);
                });
        }

        // Função para criar campos de beneficiários dinamicamente
        function criarCamposBeneficiarios() {
            var quantidadeBeneficiarios = parseInt(document.getElementById('quantidade').value);
            var divBeneficiarios = document.getElementById('beneficiarios');
            divBeneficiarios.innerHTML = '';

            for (var i = 1; i <= quantidadeBeneficiarios; i++) {
                var fieldsetBeneficiario = document.createElement('fieldset');
                fieldsetBeneficiario.innerHTML = '<legend>Beneficiário ' + i + ':</legend>' +
                    '<label for="nome_beneficiario_' + i + '">Nome:</label>' +
                    '<input type="text" id="nome_beneficiario_' + i + '" name="nome_beneficiario_' + i +
                    '" required><br>' +
                    '<label for="idade_beneficiario_' + i + '">Idade:</label>' +
                    '<input type="number" id="idade_beneficiario_' + i + '" name="idade_beneficiario_' + i +
                    '" min="0" required><br>' +
                    '<label for="plano_beneficiario_' + i + '">Plano Escolhido:</label>' +
                    '<select id="plano_beneficiario_' + i + '" name="plano_beneficiario_' + i +
                    '" required></select><br>' +
                    '<span id="valor_plano_' + i + '" class="hidden">Valor do plano: R$ 0.00</span><br><br>';

                divBeneficiarios.appendChild(fieldsetBeneficiario);
            }

            // Chamar a função para carregar os planos e preencher os seletores
            carregarPlanos();
        }

        // Função para carregar os planos do arquivo JSON e preencher o seletor
        function carregarPlanos() {
            carregarDadosJson('plans.json')
                .then(data => {
                    var selectOptions =
                    '<option value="">Escolha seu plano</option>'; // Opção vazia para ser a opção padrão
                    data.forEach(plano => {
                        selectOptions += '<option value="' + plano.codigo + '">' + plano.nome + '</option>';
                    });
                    var planoSelects = document.querySelectorAll('[id^="plano_beneficiario_"]');
                    planoSelects.forEach(select => {
                        select.innerHTML = selectOptions;
                        select.addEventListener('change', mostrarValorPlano);
                    });
                });
        }

        // Função para calcular o valor total dos planos escolhidos
        function calcularValorTotalCotacao() {
            var totalCotacao = 0;
            var planoSelects = document.querySelectorAll('[id^="plano_beneficiario_"]');
            var promises = []; // Array para armazenar as promessas das consultas de preços

            planoSelects.forEach(select => {
                var planoEscolhido = select.value;
                var idadeBeneficiario = parseInt(select.parentNode.querySelector('[id^="idade_beneficiario_"]')
                    .value);

                // Consulta assíncrona dos preços dos planos e armazenamento das promessas
                var promise = carregarDadosJson('prices.json')
                    .then(precos => {
                        // Chama a função para obter o valor do plano com base na idade
                        var valorPlano = obterValorPlano(idadeBeneficiario, planoEscolhido, precos);
                        return valorPlano;
                    });

                promises.push(promise);
            });

            // Promise.all para aguardar o resultado de todas as consultas de preços
            Promise.all(promises)
                .then(valoresPlanos => {
                    // Somar todos os valores dos planos para obter o total da cotação
                    totalCotacao = valoresPlanos.reduce((total, valor) => total + valor, 0);

                    // Atualiza o valor total na área específica do formulário
                    document.getElementById('valor_total_cotacao').textContent = 'Valor Total da Cotação: R$ ' +
                        totalCotacao.toFixed(2);
                });
        }




        // Event listener para chamar a função ao alterar a quantidade de beneficiários
        document.getElementById('quantidade').addEventListener('change', criarCamposBeneficiarios);
        document.getElementById('beneficiarios').addEventListener('change', calcularValorTotalCotacao);


        // Chamando a função inicialmente para exibir os campos iniciais
        criarCamposBeneficiarios();
        calcularValorTotalCotacao();
    </script>
</body>

</html>