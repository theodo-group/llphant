<?php

declare(strict_types=1);

namespace LLPhant\Embeddings\EmbeddingGenerator\Ollama;

use Exception;
use GuzzleHttp\Client;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\EmbeddingGenerator\EmbeddingGeneratorInterface;
use LLPhant\OllamaConfig;

use function str_replace;

final class OllamaEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public Client $client;

    private readonly string $model;

    public function __construct(OllamaConfig $config)
    {
        $this->model = $config->model;
        $this->client = new Client([
            'base_uri' => $config->url,
        ]);
    }

    /**
     * Call out to Ollama embedding endpoint.
     *
     * @return float[]
     *
     * @throws \JsonException
     * @throws Exception
     */
    public function embedText(string $text, ?int $dimensions = null): array
    {
        if ($dimensions !== null) {
            throw new Exception('Setting embeddings dimensions is not supported.');
        }

        $text = str_replace("\n", ' ', $text);

        $response = $this->client->post('embeddings', [
            'body' => json_encode([
                'model' => $this->model,
                'prompt' => $text,
            ], JSON_THROW_ON_ERROR),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $searchResults = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($searchResults)) {
            throw new Exception("Request to Ollama didn't returned an array: ".$response->getBody()->getContents());
        }

        if (! isset($searchResults['embedding'])) {
            throw new Exception("Request to Ollama didn't returned expected format: ".$response->getBody()->getContents());
        }

        return $searchResults['embedding'];
    }

    /**
     * @throws \JsonException
     */
    public function embedDocument(Document $document, ?int $dimensions = null): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text, $dimensions);

        return $document;
    }

    /**
     * @param  Document[]  $documents
     * @return Document[]
     *
     * @throws \JsonException
     */
    public function embedDocuments(array $documents, ?int $dimensions = null): array
    {
        $embedDocuments = [];
        foreach ($documents as $document) {
            $embedDocuments[] = $this->embedDocument($document, $dimensions);
        }

        return $embedDocuments;
    }

    public function getEmbeddingLength(): int
    {
        return 1024;
    }
}
