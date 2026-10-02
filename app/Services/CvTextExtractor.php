<?php

namespace App\Services;

use DOMDocument;
use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

class CvTextExtractor
{
    public function extract(string $path, string $extension): string
    {
        if ($extension === 'pdf') {
            return (new Parser())->parseFile($path)->getText();
        }

        if ($extension === 'docx') {
            return $this->extractDocx($path);
        }

        throw new RuntimeException('Le format de ce document n’est pas pris en charge.');
    }

    private function extractDocx(string $path): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La lecture des documents DOCX n’est pas disponible sur ce serveur.');
        }

        $archive = new ZipArchive();
        if ($archive->open($path) !== true) {
            throw new RuntimeException('Le document DOCX est invalide ou endommagé.');
        }

        $xml = $archive->getFromName('word/document.xml');
        $archive->close();

        if ($xml === false || trim($xml) === '') {
            throw new RuntimeException('Aucun texte lisible n’a été trouvé dans ce document DOCX.');
        }

        $document = new DOMDocument();
        $previousState = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        if (! $loaded) {
            throw new RuntimeException('Le document DOCX est invalide ou endommagé.');
        }

        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $paragraphs = $document->getElementsByTagNameNS($namespace, 'p');
        $lines = [];

        foreach ($paragraphs as $paragraph) {
            $parts = [];
            foreach ($paragraph->getElementsByTagNameNS($namespace, 't') as $textNode) {
                $parts[] = $textNode->textContent;
            }

            $line = trim(implode('', $parts));
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }
}
