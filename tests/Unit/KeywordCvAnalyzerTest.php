<?php

namespace Tests\Unit;

use App\Services\KeywordCvAnalyzer;
use RuntimeException;
use Tests\TestCase;

class KeywordCvAnalyzerTest extends TestCase
{
    public function test_it_extracts_profile_details_and_known_skills(): void
    {
        $text = <<<'CV'
Jean Marc Dupont
jean.marc@example.com
+33 6 12 34 56 78

Formation
Master informatique, spécialité développement web

Compétences
Développement avec PHP, Laravel, JavaScript, Vue.js, MySQL et Git.

Expériences professionnelles
Stage développeur web chez Exemple, création d'une application interne.

Langues
Français courant, anglais professionnel.
CV;

        $result = (new KeywordCvAnalyzer())->analyze($text);

        $this->assertSame('Jean Marc Dupont', $result['name']);
        $this->assertSame('jean.marc@example.com', $result['email']);
        $this->assertSame('Master', $result['study_level']);
        $this->assertContains('Laravel', $result['skills']);
        $this->assertContains('Vue.js', $result['technologies']);
        $this->assertContains('Français', $result['languages']);
        $this->assertNotEmpty($result['experiences']);
    }

    public function test_it_explains_when_no_relevant_information_is_found(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('aucune information de profil exploitable');

        (new KeywordCvAnalyzer())->analyze(str_repeat('Texte sans profil détectable. ', 4));
    }

    public function test_it_normalizes_legacy_pdf_text_encoding(): void
    {
        $text = "Jean Marc Dupont\nmarc@example.com\nDéveloppement PHP et Laravel\x96 expérience professionnelle";

        $result = (new KeywordCvAnalyzer())->analyze($text);

        $this->assertSame('marc@example.com', $result['email']);
        $this->assertContains('Laravel', $result['skills']);
    }
}
