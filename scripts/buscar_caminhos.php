<?php

$query = $_GET['q'] ?? '';

if (!$query) {
    echo json_encode([]);
    exit;
}

$url = 'http://elasticsearch:9200/documentos/_search';

$body = [
    '_source' => ['caminho'], // retorna somente o campo caminho
    'query' => [
        'multi_match' => [
            'query' => $query,
            'fields' => [
                'cpfCnpj^5',
                'nome_arquivo^2',
                'conteudo',
            ],
        ],
    ],
    'size' => 100,
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$prefixoLocal = '/documentos/';
$prefixoRede = 'file://192.168.15.100/arquivo/';

$caminhos = [];

foreach ($data['hits']['hits'] as $hit) {
    $caminho = $hit['_source']['caminho'];

    $caminho = str_replace($prefixoLocal, $prefixoRede, $caminho);

    $caminhos[] = $caminho;
}

header('Content-Type: application/json');
echo json_encode($caminhos, JSON_PRETTY_PRINT);
