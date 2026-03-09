<?php

$query = $_GET['q'] ?? '';

$body = [
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
];

$ch = curl_init('http://elasticsearch:9200/documentos/_search');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$resultado = curl_exec($ch);

header('Content-Type: application/json');
echo $resultado;
