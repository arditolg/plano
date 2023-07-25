<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Função para ler o conteúdo do arquivo JSON
    function lerArquivoJson($nomeArquivo)
    {
        $conteudo = file_get_contents($nomeArquivo);
        return json_decode($conteudo, true);
    }

    // Função para salvar o conteúdo no arquivo JSON
    function salvarArquivoJson($nomeArquivo, $conteudo)
    {
        $dadosJson = json_encode($conteudo);
        file_put_contents($nomeArquivo, $dadosJson);
    }

    // Função para obter o próximo valor de id_cotacao a ser usado
    function obterProximoIdCotacao()
    {
        $dadosCotacao = lerArquivoJson('proposta.json');
        $idCotacao = 1;
        if (!empty($dadosCotacao)) {
            $idCotacao = max(array_column($dadosCotacao, 'id_cotacao')) + 1;
        }
        return $idCotacao;
    }

    // Obtém os dados do formulário
    $idCotacao = obterProximoIdCotacao();
    $quantidadeBeneficiarios = $_POST['quantidade'];
    $beneficiarios = array();

    // Carrega os planos e os preços dos arquivos JSON
    $planos = lerArquivoJson('plans.json');
    $precos = lerArquivoJson('prices.json');

    // Função para determinar o valor com base na idade do beneficiário e no plano escolhido
    function obterValorPlano($idade, $plano)
    {
        global $precos;
        $valor = 0;

        if ($idade >= 0 && $idade <= 17) {
            $valor = $precos[$plano]['faixa1'];
        } elseif ($idade >= 18 && $idade <= 40) {
            $valor = $precos[$plano]['faixa2'];
        } elseif ($idade > 40) {
            $valor = $precos[$plano]['faixa3'];
        }

        return $valor;
    }

    // Verifica se o arquivo proposta.json existe e lê os dados existentes
    $dadosCotacao = array();
    if (file_exists('proposta.json')) {
        $dadosCotacao = lerArquivoJson('proposta.json');
    }

    $idCotacao++; // Incrementa o id_cotacao

    for ($i = 1; $i <= $quantidadeBeneficiarios; $i++) {
        $nomeBeneficiario = $_POST['nome_beneficiario_' . $i];
        $idadeBeneficiario = $_POST['idade_beneficiario_' . $i];
        $planoBeneficiario = $_POST['plano_beneficiario_' . $i];

        // Verifica se o plano selecionado está presente no arquivo plans.json
        if (!isset($planos[$planoBeneficiario])) {
            echo "Plano inválido para o beneficiário $nomeBeneficiario.";
            exit;
        }

        // Obtém o valor do plano com base na idade do beneficiário
        $valorPlanoBeneficiario = obterValorPlano($idadeBeneficiario, $planoBeneficiario);

        $beneficiarios[] = array(
            'id_cotacao' => $idCotacao,
            'nome' => $nomeBeneficiario,
            'idade' => $idadeBeneficiario,
            'plano' => $planoBeneficiario,
            'valor_plano' => $valorPlanoBeneficiario
        );
    }

    $dadosCotacao[] = array(
        'id_cotacao' => $idCotacao,
        'quantidadeBeneficiarios' => $quantidadeBeneficiarios,
        'beneficiarios' => $beneficiarios
    );

    // Converte os dados para JSON
    $dadosJson = json_encode($dadosCotacao);

    // Salva os dados no arquivo proposta.json
    $nomeArquivo = 'proposta.json';
    file_put_contents($nomeArquivo, $dadosJson);

    // Lê os dados existentes, se houver
    $dadosCotacao = lerArquivoJson('proposta.json');

    // Adiciona a nova cotação ao array de cotações existentes
    $novaCotacao = array(
        'id_cotacao' => $idCotacao,
        'quantidadeBeneficiarios' => $quantidadeBeneficiarios,
        'beneficiarios' => $beneficiarios
    );

    $dadosCotacao[] = $novaCotacao;

    // Salva os dados no arquivo proposta.json
    salvarArquivoJson('proposta.json', $dadosCotacao);

    echo "Dados salvos com sucesso em $nomeArquivo";
} else {
    echo "Erro: método inválido.";
}
?>