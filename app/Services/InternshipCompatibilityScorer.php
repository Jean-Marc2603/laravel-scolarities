<?php

namespace App\Services;

use Illuminate\Support\Str;

class InternshipCompatibilityScorer
{
    /**
     * Compare a student's detected skills with an offer's requirements.
     * The score represents the share of required skills present on the CV.
     *
     * @param  array<int, string>  $cvSkills
     * @param  array<int, string>  $offerSkills
     * @return array{score: int, matched: array<int, string>, missing: array<int, string>}
     */
    public function compare(array $cvSkills, array $offerSkills): array
    {
        $normalizedCvSkills = [];
        foreach ($cvSkills as $skill) {
            $normalizedCvSkills[$this->normalize($skill)] = true;
        }

        $requiredSkills = [];
        foreach ($offerSkills as $skill) {
            $normalizedSkill = $this->normalize($skill);
            if ($normalizedSkill !== '') {
                $requiredSkills[$normalizedSkill] = $requiredSkills[$normalizedSkill] ?? trim($skill);
            }
        }

        $matched = [];
        $missing = [];

        foreach ($requiredSkills as $normalizedSkill => $requiredSkill) {
            if (isset($normalizedCvSkills[$normalizedSkill])) {
                $matched[] = $requiredSkill;
            } else {
                $missing[] = $requiredSkill;
            }
        }

        $score = count($requiredSkills) === 0
            ? 0
            : (int) round((count($matched) / count($requiredSkills)) * 100);

        return [
            'score' => $score,
            'matched' => $matched,
            'missing' => $missing,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $offers
     * @return array<int, array<string, mixed>>
     */
    public function sortOffersByScore(array $offers): array
    {
        usort($offers, function (array $first, array $second) {
            return $second['compatibility']['score'] <=> $first['compatibility']['score'];
        });

        return $offers;
    }

    private function normalize(string $skill): string
    {
        return Str::lower(trim(Str::ascii($skill)));
    }
}
