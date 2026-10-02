<?php

namespace Tests\Unit;

use App\Services\InternshipCompatibilityScorer;
use Tests\TestCase;

class InternshipCompatibilityScorerTest extends TestCase
{
    public function test_it_scores_matching_and_missing_skills_as_a_percentage_of_offer_requirements(): void
    {
        $result = (new InternshipCompatibilityScorer())->compare(
            ['PHP', 'Laravel', 'MySQL', 'JavaScript'],
            ['PHP', 'Laravel', 'Vue.js', 'MySQL']
        );

        $this->assertSame(75, $result['score']);
        $this->assertSame(['PHP', 'Laravel', 'MySQL'], $result['matched']);
        $this->assertSame(['Vue.js'], $result['missing']);
    }

    public function test_skill_matching_ignores_case_and_duplicate_requirements(): void
    {
        $result = (new InternshipCompatibilityScorer())->compare(
            ['laravel', ' php '],
            ['Laravel', 'PHP', 'PHP']
        );

        $this->assertSame(100, $result['score']);
        $this->assertSame(['Laravel', 'PHP'], $result['matched']);
        $this->assertSame([], $result['missing']);
    }

    public function test_empty_offer_requirements_produce_a_zero_score(): void
    {
        $result = (new InternshipCompatibilityScorer())->compare(['PHP'], []);

        $this->assertSame(0, $result['score']);
        $this->assertSame([], $result['matched']);
        $this->assertSame([], $result['missing']);
    }

    public function test_it_sorts_offers_by_compatibility_score_descending(): void
    {
        $scorer = new InternshipCompatibilityScorer();
        $offers = [
            ['title' => 'Score faible', 'compatibility' => ['score' => 25]],
            ['title' => 'Score élevé', 'compatibility' => ['score' => 100]],
            ['title' => 'Score moyen', 'compatibility' => ['score' => 75]],
        ];

        $sorted = $scorer->sortOffersByScore($offers);

        $this->assertSame(['Score élevé', 'Score moyen', 'Score faible'], array_column($sorted, 'title'));
    }
}
