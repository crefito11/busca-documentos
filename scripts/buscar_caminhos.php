<?php

$query = $_GET['q'] ?? '';

if (!$query) {
    echo json_encode([]);
    exit;
}

function normalizar($texto)
{
    $texto = strtolower($texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

$queryNormalizada = normalizar($query);

$url = 'http://elasticsearch:9200/documentos/_search';

/* $body = [
    '_source' => ['caminho', 'conteudo', 'nome_arquivo', 'cpfCnpj'],
    'size' => 50,
    'query' => [
        'bool' => [
            'should' => [
                [
                    'term' => [
                        'cpfCnpj' => [
                            'value' => $query,
                            'boost' => 10,
                        ],
                    ],
                ],

                [
                    'match_phrase' => [
                        'nome_arquivo' => [
                            'query' => $query,
                            'boost' => 8,
                        ],
                    ],
                ],

                [
                    'match_phrase' => [
                        'conteudo' => [
                            'query' => $query,
                            'boost' => 5,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'sort' => [
        '_score',
    ],
]; */
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

$prefixoArquivoAntigo = '\\192.168.15.100/arquivos servidor antigo/ARQUIVO';
$prefixoRegistroGo = '\\192.168.15.100/Goiania/ARQUIVOS GOIANIA';
$prefixoPadrao = '\\192.168.15.100/arquivo/';

$caminhos = [];

foreach ($data['hits']['hits'] as $hit) {
    $conteudo = normalizar($hit['_source']['conteudo'] ?? '');
    $arquivo = normalizar($hit['_source']['nome_arquivo'] ?? '');

    if (
        str_contains($conteudo, $queryNormalizada)
        || str_contains($arquivo, $queryNormalizada)
        || ($hit['_source']['cpfCnpj'] ?? '') === $query
    ) {
        $caminho = $hit['_source']['caminho'];

        if (str_starts_with($caminho, '/documentos/ARQUIVOS GOIANIA')) {
            $caminho = str_replace('/documentos/ARQUIVOS GOIANIA', $prefixoRegistroGo, $caminho);
        } elseif (str_starts_with($caminho, '/documentos/ARQUIVO')) {
            $caminho = str_replace('/documentos/ARQUIVO', $prefixoArquivoAntigo, $caminho);
        } else {
            $caminho = str_replace($prefixoLocal, $prefixoPadrao, $caminho);
        }

        $caminhos[] = $caminho;
    }
}

header('Content-Type: application/json');
echo json_encode(array_values(array_unique($caminhos)), JSON_PRETTY_PRINT);
