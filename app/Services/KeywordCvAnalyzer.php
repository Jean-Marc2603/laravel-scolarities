<?php

namespace App\Services;

use App\Contracts\CvAnalyzer;
use RuntimeException;

class KeywordCvAnalyzer implements CvAnalyzer
{
    private const SKILLS = [
        'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'MySQL', 'Java', 'Python',
        'Git', 'HTML', 'CSS', 'Bootstrap', 'C#', 'SQL', 'UML', 'TypeScript', 'Node.js',
        'C++', 'C', 'Tailwind CSS', 'jQuery', 'Angular', 'PostgreSQL', 'MongoDB',
        'Docker', 'Linux', 'Figma', 'WordPress', 'Symfony', 'Django', 'Spring',
    ];

    private const TECHNOLOGIES = [
        'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'MySQL', 'Java', 'Python',
        'Git', 'HTML', 'CSS', 'Bootstrap', 'C#', 'SQL', 'TypeScript', 'Node.js',
        'C++', 'C', 'Tailwind CSS', 'jQuery', 'Angular', 'PostgreSQL', 'MongoDB',
        'Docker', 'Linux', 'WordPress', 'Symfony', 'Django', 'Spring',
    ];

    private const SECTION_HEADINGS = [
        'formation' => ['formation', 'education', 'études', 'etudes', 'parcours académique', 'parcours academique'],
        'experience' => ['expérience', 'experience', 'expériences professionnelles', 'experiences professionnelles', 'parcours professionnel'],
        'languages' => ['langue', 'langues', 'languages', 'language'],
        'interests' => ['centre d’intérêt', 'centres d’intérêt', 'centre d\'interet', 'centres d\'interet', 'loisirs', 'interests', 'hobbies'],
        'skills' => ['compétence', 'competence', 'skills', 'technologies', 'expertise'],
    ];

    /** @return array<string, mixed> */
    public function analyze(string $text): array
    {
        $text = $this->normalizeText($text);
        if (mb_strlen($text) < 40 || ! preg_match('/[\p{L}]{3,}/u', $text)) {
            throw new RuntimeException('Le document ne contient pas assez de texte exploitable. Essayez un PDF texte ou un DOCX non protégé.');
        }

        $sections = $this->extractSections($text);
        $skills = $this->findKeywords($text, self::SKILLS);
        $technologies = $this->findKeywords($text, self::TECHNOLOGIES);

        $analysis = [
            'name' => $this->findName($text),
            'email' => $this->findFirst('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text),
            'phone' => $this->findFirst('/(?:\+?\d[\d\s().\/-]{7,}\d)/u', $text),
            'formation' => $this->summarize($sections['formation'] ?? '', 500),
            'study_level' => $this->findStudyLevel($text),
            'domain' => $this->findDomain($text, $sections),
            'skills' => $skills,
            'technologies' => $technologies,
            'experiences' => $this->splitItems($sections['experience'] ?? '', 6),
            'languages' => $this->extractLanguages($text, $sections['languages'] ?? ''),
            'interests' => $this->summarize($sections['interests'] ?? '', 300),
        ];

        $hasRelevantInformation = ! empty($analysis['email']) || ! empty($analysis['phone']) ||
            ! empty($analysis['formation']) || ! empty($analysis['skills']) ||
            ! empty($analysis['experiences']) || ! empty($analysis['languages']);

        if (! $hasRelevantInformation) {
            throw new RuntimeException('Le texte a été extrait, mais aucune information de profil exploitable n’a été détectée. Vérifiez que le CV contient du texte sélectionnable et des rubriques lisibles.');
        }

        return $analysis;
    }

    private function normalizeText(string $text): string
    {
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }

        $text = str_replace(["\r\n", "\r", "\t", "\0"], ["\n", "\n", ' ', ' '], $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
        $text = preg_replace('/[ ]{2,}/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }

    /** @return array<string, string> */
    private function extractSections(string $text): array
    {
        $lines = preg_split('/\n/u', $text) ?: [];
        $sectionNames = [];
        foreach (self::SECTION_HEADINGS as $key => $headings) {
            foreach ($headings as $heading) {
                $sectionNames[$key][] = $heading;
            }
        }

        $sections = [];
        $current = null;
        foreach ($lines as $line) {
            $trimmed = trim($line, " \t•*-–—:|\r");
            $matched = null;

            foreach ($sectionNames as $key => $headings) {
                foreach ($headings as $heading) {
                    if (mb_strtolower($trimmed) === mb_strtolower($heading) ||
                        preg_match('/^'.preg_quote($heading, '/').'\s*[:–—-]\s*$/iu', $trimmed)) {
                        $matched = $key;
                        break 2;
                    }
                }
            }

            if ($matched !== null) {
                $current = $matched;
                $sections[$current] = $sections[$current] ?? '';

                continue;
            }

            if ($current !== null && $trimmed !== '') {
                $sections[$current] .= ($sections[$current] === '' ? '' : "\n").$trimmed;
            }
        }

        return $sections;
    }

    /** @param  array<int, string>  $keywords @return array<int, string> */
    private function findKeywords(string $text, array $keywords): array
    {
        $found = [];
        foreach ($keywords as $keyword) {
            $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($keyword, '/').'(?![\p{L}\p{N}])/iu';
            if (preg_match($pattern, $text)) {
                $found[] = $keyword;
            }
        }

        return $found;
    }

    private function findName(string $text): ?string
    {
        $lines = preg_split('/\n/u', $text) ?: [];
        foreach (array_slice($lines, 0, 12) as $line) {
            $candidate = trim($line, " \t•*-–—|\r");
            if ($candidate === '' || mb_strlen($candidate) > 70 ||
                preg_match('/@|\d|curriculum|\bcv\b|résumé|resume|portfolio|téléphone|telephone|email|développeur|developpeur|developer|étudiant|etudiant|ingénieur|ingenieur|designer|informatique|développement|developpement|\bweb\b|master|licence|bachelor|université|universite|formation|expérience|experience|compétences|competences|ingénierie|ingenierie|\bscience\b|management/i', $candidate)) {
                continue;
            }

            $words = preg_split('/\s+/u', $candidate) ?: [];
            if (count($words) >= 2 && count($words) <= 5 && preg_match('/^[\p{L}][\p{L}\x{27}\x{2019}-]*(?:\s+[\p{L}][\p{L}\x{27}\x{2019}-]*){1,4}$/u', $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function findStudyLevel(string $text): ?string
    {
        $levels = [
            '/\b(doctorat|ph\.?d\.?|doctorate)\b/iu' => 'Doctorat',
            '/\b(master(?:\s+(?:2|1|degree))?|mastère|msc|mba)\b/iu' => 'Master',
            '/\b(licence|bachelor|baccalauréat\s*\+\s*3|bac\s*\+\s*3)\b/iu' => 'Licence / Bachelor',
            '/\b(bts|dut|but|deug|bac\s*\+\s*2)\b/iu' => 'Bac +2',
            '/\b(baccalauréat|bac\s*\+\s*\d)\b/iu' => 'Enseignement supérieur',
        ];

        foreach ($levels as $pattern => $label) {
            if (preg_match($pattern, $text)) {
                return $label;
            }
        }

        return null;
    }

    /** @param  array<string, string>  $sections */
    private function findDomain(string $text, array $sections): ?string
    {
        $domainMap = [
            'informatique / développement' => ['informatique', 'développement web', 'developpement web', 'software', 'programmation', 'développeur', 'developpeur'],
            'réseaux et systèmes' => ['réseau', 'reseau', 'système', 'systeme', 'cybersécurité', 'cybersecurite'],
            'gestion / comptabilité' => ['comptabilité', 'comptabilite', 'gestion', 'finance', 'audit'],
            'marketing / communication' => ['marketing', 'communication', 'seo', 'social media'],
            'design' => ['design', 'graphisme', 'ui/ux', 'ux/ui'],
            'data / intelligence artificielle' => ['data science', 'machine learning', 'intelligence artificielle', 'analyse de données', 'analyse de donnees'],
        ];
        $search = ($sections['formation'] ?? '')."\n".$text;

        foreach ($domainMap as $domain => $terms) {
            foreach ($terms as $term) {
                if (preg_match('/'.preg_quote($term, '/').'/iu', $search)) {
                    return ucfirst($domain);
                }
            }
        }

        return null;
    }

    private function extractLanguages(string $text, string $section): array
    {
        $languages = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien', 'Portugais', 'Arabe', 'Chinois', 'Japonais', 'Néerlandais', 'Swahili'];
        $search = $section !== '' ? $section : $text;

        return $this->findKeywords($search, $languages);
    }

    /** @return array<int, string> */
    private function splitItems(string $text, int $limit): array
    {
        if ($text === '') {
            return [];
        }

        $items = preg_split('/\n+|(?<=[.!?])\s+(?=[\p{Lu}\d])/u', $text) ?: [];
        $items = array_values(array_filter(array_map(function ($item) {
            return trim(preg_replace('/\s+/u', ' ', $item) ?? $item, " \t•*-–—|\r");
        }, $items), function ($item) {
            return mb_strlen($item) >= 12;
        }));

        return array_slice(array_unique($items), 0, $limit);
    }

    private function summarize(string $text, int $limit): ?string
    {
        $summary = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($summary === '') {
            return null;
        }

        return mb_strlen($summary) > $limit ? mb_substr($summary, 0, $limit - 1).'…' : $summary;
    }

    private function findFirst(string $pattern, string $text): ?string
    {
        return preg_match($pattern, $text, $matches) ? trim($matches[0]) : null;
    }
}
