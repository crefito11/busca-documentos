<?php

$base = '/documentos';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $arquivo) {
    if (!$arquivo->isFile()) {
        continue;
    }

    if ($arquivo->getExtension() !== 'pdf') {
        continue;
    }

    $caminho = $arquivo->getPathname();
    $nomeArquivo = $arquivo->getFilename();

    echo "Verificando: $nomeArquivo\n";

    $hash = md5_file($caminho);

    echo "Indexando: $nomeArquivo\n";

    // extrair texto com Tika
    $ch = curl_init('http://tika:9998/tika/text');
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_INFILE, fopen($caminho, 'r'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $texto = curl_exec($ch);
    curl_close($ch);

    $texto = strip_tags($texto); // remove html caso exista
    $texto = trim($texto);

    // extrair CPF
    preg_match('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $texto, $cpfEncontrado);
    preg_match('/\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/', $texto, $cnpjEncontrado);
    $cpf = $cpfEncontrado[0] ?? null;
    $cnpj = $cnpjEncontrado[0] ?? null;

    $dados = [
        'nome_arquivo' => $nomeArquivo,
        'cpfCnpj' => empty($cpf) ? $cnpj : $cpf,
        'caminho' => $caminho,
        'conteudo' => $texto,
    ];

    $json = json_encode($dados);

    $url = "http://elasticsearch:9200/documentos/_doc/$hash";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    echo "Indexado!\n\n";
}
