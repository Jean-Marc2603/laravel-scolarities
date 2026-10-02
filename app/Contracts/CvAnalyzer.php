<?php

namespace App\Contracts;

interface CvAnalyzer
{
    /**
     * Analyze extracted CV text and return normalized profile data.
     *
     * @return array<string, mixed>
     */
    public function analyze(string $text): array;
}
