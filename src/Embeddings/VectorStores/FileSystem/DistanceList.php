<?php

namespace LLPhant\Embeddings\VectorStores\FileSystem;

use LLPhant\Embeddings\Document;

class DistanceList
{
    /**
     * @var array<array{dist: float, document: Document}>
     */
    private array $list = [];

    public function __construct(private readonly int $listLength)
    {
    }

    public function addDistance(float $dist, Document $document): void
    {
        $this->list[] = ['dist' => $dist, 'document' => $document];
        \usort($this->list, fn (array $a, array $b): int => $a['dist'] <=> $b['dist']);
        $this->list = \array_slice($this->list, 0, $this->listLength);
    }

    /**
     * @return array<Document>
     */
    public function getDocuments(): array
    {
        return \array_map(fn (array $list): Document => $list['document'], $this->list);
    }
}
