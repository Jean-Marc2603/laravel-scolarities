<?php

namespace App\Services;

use Illuminate\Support\Str;

class InternshipOfferCatalog
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $offers = [
            ['title' => 'Développeur Web Laravel', 'company' => 'Tech Solutions', 'domain' => 'Développement web', 'location' => 'Antananarivo', 'duration' => '3 mois', 'description' => 'Participez à la conception d’applications web et à l’amélioration de services numériques utilisés au quotidien.', 'skills' => ['Laravel', 'PHP', 'MySQL', 'Git'], 'days' => 18, 'details' => 'Vous contribuerez au développement de nouvelles fonctionnalités, à la correction de bugs et aux revues de code avec une équipe expérimentée.'],
            ['title' => 'Stagiaire développeur front-end', 'company' => 'Pixel Factory', 'domain' => 'Développement web', 'location' => 'Antananarivo', 'duration' => '4 mois', 'description' => 'Aidez à créer des interfaces rapides, accessibles et agréables pour les utilisateurs de nos produits web.', 'skills' => ['JavaScript', 'Vue.js', 'HTML', 'CSS'], 'days' => 24, 'details' => 'Vous participerez à l’intégration de maquettes, aux tests d’interface et à la maintenance d’un design system.'],
            ['title' => 'Développeur full-stack junior', 'company' => 'Mada Digital Lab', 'domain' => 'Développement web', 'location' => 'Toamasina', 'duration' => '6 mois', 'description' => 'Rejoignez une petite équipe produit pour faire évoluer une plateforme de gestion destinée aux PME.', 'skills' => ['React', 'Node.js', 'PostgreSQL', 'Git'], 'days' => 31, 'details' => 'Le stage couvre le développement front et back, la documentation technique et la mise en place de tests automatisés.'],
            ['title' => 'Stagiaire développement logiciel', 'company' => 'Softia', 'domain' => 'Développement logiciel', 'location' => 'Antananarivo', 'duration' => '5 mois', 'description' => 'Contribuez au développement et à la qualité d’un logiciel métier en collaboration avec l’équipe technique.', 'skills' => ['Java', 'Spring', 'SQL', 'Git'], 'days' => 28, 'details' => 'Vous découvrirez les méthodes agiles, la conception logicielle et les bonnes pratiques de tests et de revue de code.'],
            ['title' => 'Développeur Python – outils internes', 'company' => 'Innova Group', 'domain' => 'Développement logiciel', 'location' => 'Fianarantsoa', 'duration' => '3 mois', 'description' => 'Automatisez des tâches internes et développez des outils pour simplifier les processus de l’entreprise.', 'skills' => ['Python', 'SQL', 'Git', 'Linux'], 'days' => 20, 'details' => 'Le ou la stagiaire participera au recueil des besoins, au prototypage et à la documentation des outils développés.'],
            ['title' => 'Technicien systèmes et réseaux', 'company' => 'Connectis', 'domain' => 'Réseaux et systèmes', 'location' => 'Mahajanga', 'duration' => '4 mois', 'description' => 'Accompagnez l’équipe infrastructure dans la supervision et l’évolution du parc informatique.', 'skills' => ['Linux', 'Réseaux', 'Windows', 'Support IT'], 'days' => 26, 'details' => 'Le stage permettra de participer au diagnostic d’incidents, à l’inventaire du matériel et à la mise à jour des procédures.'],
            ['title' => 'Assistant administrateur réseau', 'company' => 'Telma Services', 'domain' => 'Réseaux et systèmes', 'location' => 'Antananarivo', 'duration' => '6 mois', 'description' => 'Participez au maintien en condition opérationnelle des réseaux et services informatiques.', 'skills' => ['TCP/IP', 'Linux', 'Cisco', 'Sécurité'], 'days' => 34, 'details' => 'Vous accompagnerez les techniciens lors des interventions et contribuerez au suivi des équipements réseau.'],
            ['title' => 'Analyste junior en cybersécurité', 'company' => 'Secure Mada', 'domain' => 'Cybersécurité', 'location' => 'Antananarivo', 'duration' => '5 mois', 'description' => 'Aidez à analyser les alertes de sécurité et à sensibiliser les équipes aux bonnes pratiques numériques.', 'skills' => ['Sécurité', 'Linux', 'Réseaux', 'Python'], 'days' => 21, 'details' => 'Le stage inclut la veille sur les vulnérabilités, l’analyse de journaux et la rédaction de rapports de sécurité.'],
            ['title' => 'Stagiaire gouvernance sécurité', 'company' => 'Audit & Risk', 'domain' => 'Cybersécurité', 'location' => 'Antsirabe', 'duration' => '3 mois', 'description' => 'Contribuez à la cartographie des risques et à l’amélioration des procédures de sécurité des données.', 'skills' => ['ISO 27001', 'Analyse de risques', 'Rédaction', 'Excel'], 'days' => 29, 'details' => 'Vous aiderez à documenter les contrôles existants et à préparer des supports de sensibilisation.'],
            ['title' => 'Assistant data analyst', 'company' => 'Data Horizon', 'domain' => 'Data / Intelligence artificielle', 'location' => 'Antananarivo', 'duration' => '4 mois', 'description' => 'Transformez des données opérationnelles en tableaux de bord et en analyses utiles aux équipes métier.', 'skills' => ['Python', 'SQL', 'Power BI', 'Excel'], 'days' => 22, 'details' => 'Vous préparerez les jeux de données, réaliserez des analyses exploratoires et présenterez vos conclusions.'],
            ['title' => 'Stagiaire machine learning', 'company' => 'AI Works', 'domain' => 'Data / Intelligence artificielle', 'location' => 'Fianarantsoa', 'duration' => '6 mois', 'description' => 'Explorez des cas d’usage de machine learning et contribuez à la préparation de prototypes.', 'skills' => ['Python', 'Machine learning', 'Pandas', 'SQL'], 'days' => 36, 'details' => 'Vous travaillerez sur la préparation de données, l’évaluation de modèles et la restitution des résultats.'],
            ['title' => 'Assistant marketing digital', 'company' => 'Comète Communication', 'domain' => 'Marketing digital', 'location' => 'Antananarivo', 'duration' => '3 mois', 'description' => 'Participez à l’animation des réseaux sociaux et au suivi des campagnes de communication digitale.', 'skills' => ['Réseaux sociaux', 'Rédaction', 'Canva', 'Analytics'], 'days' => 15, 'details' => 'Vous contribuerez au calendrier éditorial, à la création de contenus et à l’analyse des performances des campagnes.'],
            ['title' => 'Stagiaire SEO et contenu', 'company' => 'Web Impact', 'domain' => 'Marketing digital', 'location' => 'Toamasina', 'duration' => '4 mois', 'description' => 'Aidez à améliorer la visibilité de sites web grâce à des contenus pertinents et au référencement naturel.', 'skills' => ['SEO', 'Rédaction web', 'Analytics', 'WordPress'], 'days' => 32, 'details' => 'Le stage couvre la recherche de mots-clés, l’optimisation de pages et le suivi des positions et du trafic.'],
            ['title' => 'Assistant comptable', 'company' => 'Cabinet RAZA', 'domain' => 'Finance / Comptabilité', 'location' => 'Antananarivo', 'duration' => '3 mois', 'description' => 'Appuyez l’équipe comptable dans le suivi des pièces, la saisie et la préparation des dossiers clients.', 'skills' => ['Comptabilité', 'Excel', 'Rigueur', 'Sage'], 'days' => 19, 'details' => 'Vous participerez au classement des pièces, au rapprochement des documents et à la préparation de tableaux de suivi.'],
            ['title' => 'Analyste financier stagiaire', 'company' => 'Mirova Finance', 'domain' => 'Finance / Comptabilité', 'location' => 'Mahajanga', 'duration' => '6 mois', 'description' => 'Contribuez à l’analyse des indicateurs financiers et à la préparation de reportings périodiques.', 'skills' => ['Finance', 'Excel', 'Analyse', 'Power BI'], 'days' => 30, 'details' => 'Vous aiderez à consolider les données et à produire des synthèses pour les équipes de gestion.'],
            ['title' => 'Assistant ressources humaines', 'company' => 'People First', 'domain' => 'Ressources humaines', 'location' => 'Antananarivo', 'duration' => '4 mois', 'description' => 'Participez aux activités de recrutement, d’intégration et de suivi administratif des collaborateurs.', 'skills' => ['Recrutement', 'Communication', 'Organisation', 'Excel'], 'days' => 25, 'details' => 'Vous contribuerez au tri des candidatures, à la planification des entretiens et à la préparation de l’accueil des nouveaux arrivants.'],
            ['title' => 'Chargé de recrutement junior', 'company' => 'Talentia', 'domain' => 'Ressources humaines', 'location' => 'Antsirabe', 'duration' => '3 mois', 'description' => 'Accompagnez les consultants dans la recherche de candidats et la qualification des profils.', 'skills' => ['Sourcing', 'Réseaux sociaux', 'Entretien', 'Rédaction'], 'days' => 27, 'details' => 'Le stage permettra de découvrir les outils de sourcing et les étapes d’un processus de recrutement.'],
            ['title' => 'Assistant chef de projet', 'company' => 'Projecta', 'domain' => 'Gestion de projet', 'location' => 'Antananarivo', 'duration' => '5 mois', 'description' => 'Aidez à coordonner les étapes de projets numériques et à assurer le suivi des actions de l’équipe.', 'skills' => ['Organisation', 'Agile', 'Trello', 'Communication'], 'days' => 23, 'details' => 'Vous préparerez les réunions de suivi, mettrez à jour les tableaux d’avancement et faciliterez la circulation des informations.'],
            ['title' => 'Assistant PMO', 'company' => 'Groupe Avenir', 'domain' => 'Gestion de projet', 'location' => 'Fianarantsoa', 'duration' => '6 mois', 'description' => 'Contribuez au suivi des indicateurs et à la structuration des outils de pilotage de projets.', 'skills' => ['Excel', 'PowerPoint', 'Analyse', 'Gestion de projet'], 'days' => 33, 'details' => 'Vous participerez à la consolidation des plannings, au suivi des risques et à la préparation des reportings.'],
            ['title' => 'Designer UI/UX stagiaire', 'company' => 'Studio Kanto', 'domain' => 'Design / UI-UX', 'location' => 'Antananarivo', 'duration' => '4 mois', 'description' => 'Imaginez des parcours simples et des interfaces cohérentes pour les produits numériques de nos clients.', 'skills' => ['Figma', 'UI/UX', 'Prototypage', 'Design system'], 'days' => 17, 'details' => 'Vous participerez aux ateliers de conception, à la création de prototypes et aux tests utilisateurs.'],
        ];

        foreach ($offers as &$offer) {
            $offer['id'] = Str::slug($offer['title']);
            $offer['deadline'] = now()->addDays($offer['days']);
            unset($offer['days']);
        }
        unset($offer);

        return $offers;
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        foreach ($this->all() as $offer) {
            if ($offer['id'] === $id) {
                return $offer;
            }
        }

        return null;
    }
}
