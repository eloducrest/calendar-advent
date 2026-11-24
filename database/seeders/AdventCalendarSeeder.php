<?php

namespace Database\Seeders;

use App\Models\CalendarDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AdventCalendarSeeder extends Seeder
{
    /**
     * Sample French challenges and gifts for the 24 days of December,
     * waiting to be personalized from the edit screen.
     */
    public function run(): void
    {
        $riddle = fn (string $question, array $acceptedAnswers): array => [
            'question' => $question,
            'accepted_answers' => $acceptedAnswers,
        ];

        $qcm = fn (string $question, array $options, int $correctIndex): array => [
            'question' => $question,
            'options' => $options,
            'correct_index' => $correctIndex,
        ];

        $game = function (string $variant, string $title, string $instructions, array $symbols = []): array {
            return array_merge([
                'game' => $variant,
                'title' => $title,
                'instructions' => $instructions,
            ], $variant === 'memory' ? ['symbols' => $symbols] : []);
        };

        $days = [
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Il descend chaque année sans escalier et passe par la cheminée sans se salir. Qui est-il ?',
                    ['santa claus', 'père noel', 'papa noel', 'santa', 'noel'],
                ),
                'gift_title' => 'La première surprise',
                'gift_message' => 'Joyeux décembre ! Voici ton premier cadeau, et ce n\'est que le début...',
                'gift_link_url' => 'https://example.com/cadeau-1',
                'gift_link_label' => 'Ouvrir ton premier cadeaubon',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_GAME,
                'challenge_data' => $game(
                    'memory',
                    'Trouve les paires',
                    'Retourne les cartes et retrouve toutes les paires.',
                    ['🎄', '⭐', '🎁', '❄️', '🔔', '🕯️'],
                ),
                'gift_title' => 'Le cadeaubon gourmand',
                'gift_message' => 'On commence en douceur : bien joué pour les paires !',
                'gift_link_url' => 'https://example.com/cadeau-2',
                'gift_link_label' => 'Utiliser mon bon',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Je suis blanc, je tombe du ciel et je fond dans la main. Qui suis-je ?',
                    ['neige', 'la neige', 'flocon', 'un flocon'],
                ),
                'gift_title' => 'La bouteille mystère',
                'gift_message' => 'Résolu(e) déjà ! Ton cadeau t\'attend.',
                'gift_link_url' => 'https://example.com/cadeau-3',
                'gift_link_label' => 'Voir le cadeau',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_QCM,
                'challenge_data' => $qcm(
                    'Quelle est la couleur du nez du renne du Père Noël ?',
                    ['Rouge', 'Bleu', 'Vert'],
                    0,
                ),
                'gift_title' => 'La carte cadeau ciné',
                'gift_message' => 'Deux places pour aller voir un film au chaud.',
                'gift_link_url' => 'https://example.com/cadeau-4',
                'gift_link_label' => 'Réserver ma séance',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Je brille sans feu, j\'orne le sapin et j\'ai toujours faim... de courant. Suis-je ?',
                    ['guirlande', 'une guirlande', 'la guirlande'],
                ),
                'gift_title' => 'Le paquet surprise n°5',
                'gift_message' => 'Continue comme ça, la suite est encore mieux !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_GAME,
                'challenge_data' => $game(
                    'simon',
                    'Simon du 6 décembre',
                    'Observe la séquence de couleurs puis répète-la, sur 5 manches.',
                ),
                'gift_title' => 'Le réveil musical',
                'gift_message' => 'Cérébralement vainqueur, voici ta récompense.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Il a une barbe blanche, un traîneau, mais ce n\'est pas ton grand-père. Qui est-ce ?',
                    ['père noel', 'papa noel', 'santa'],
                ),
                'gift_title' => 'Le droit de décorer le sapin… et ça :',
                'gift_message' => 'Tu as mérité chaque guirlande.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_QCM,
                'challenge_data' => $qcm(
                    'Combien de jours dure traditionnellement l\'Avent ?',
                    ['24', '28', '31'],
                    0,
                ),
                'gift_title' => 'La soirée cocooning',
                'gift_message' => 'Plaid, chocolat chaud et film : c\'est offert.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Petit homme sucré en pain d\'épice, on croque ses jambes en premier. Qui est-il ?',
                    ["bonhomme pain d'epice", "pain d'epice", 'bonhomme'],
                ),
                'gift_title' => 'Le bon d\'achat n°9',
                'gift_message' => 'Valable partout, à dépenser sans modération.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_GAME,
                'challenge_data' => $game(
                    'mole',
                    'Frappe-Taupe de Noël',
                    'Frappe 10 taupes qui sortent des trous de neige !',
                ),
                'gift_title' => 'Le playlist de Noël',
                'gift_message' => 'Mémoire de champion(ne) !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Je me déroule à Noël, j\'emballe les paquets et je fais peur aux chats. Suis-je ?',
                    ['ruban', 'le ruban', 'un ruban'],
                ),
                'gift_title' => 'Le paquet n°11',
                'gift_message' => 'Un petit quelque chose pour patienter.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_QCM,
                'challenge_data' => $qcm(
                    'Dans « Mon beau sapin », que porte le sapin ?',
                    ["Un vêtement d'été", "Des vêtements d'hiver", 'Un chapeau'],
                    1,
                ),
                'gift_title' => 'Le certificat expérience',
                'gift_message' => 'Une sortie que tu n\'oublieras pas.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Il guide le traîneau la nuit et son nez éclaire le chemin. Qui est-il ?',
                    ['rudolph', 'rudolphe', 'renne au nez rouge'],
                ),
                'gift_title' => 'Cadeau du 13',
                'gift_message' => 'Un vendredi 13 qui te porte chance !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'On me plante, on me décore, on me chante, puis on me jette en janvier. Suis-je ?',
                    ['sapin', 'un sapin', 'le sapin'],
                ),
                'gift_title' => 'Le panier gourmand',
                'gift_message' => 'Bonnes choses à déguster ensemble.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_GAME,
                'challenge_data' => $game(
                    'puzzle',
                    'Taquin de Noël',
                    'Fais glisser les tuiles pour remettre le tableau dans l\'ordre.',
                ),
                'gift_title' => 'Le dîner aux chandelles',
                'gift_message' => 'Moitié du chemin, 100 % des félicitations.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_QCM,
                'challenge_data' => $qcm(
                    'Quelle est la date traditionnelle de la Saint-Nicolas ?',
                    ['6 novembre', '6 décembre', '25 décembre'],
                    1,
                ),
                'gift_title' => 'La surprise sucrée',
                'gift_message' => 'Des douceurs pour les papilles.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'On me met au sommet du sapin, je brille et je guide. Suis-je ? (6 lettres)',
                    ['etoile', 'une etoile', "l'etoile", 'étoile'],
                ),
                'gift_title' => 'L\'étoile du 17',
                'gift_message' => 'Tu brilles !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Il est tout en plumes, il a un ventre blanc et marche d\'un pas dandiné sur la glace. Qui est-ce ?',
                    ['pingouin', 'manchot', 'un pingouin'],
                ),
                'gift_title' => 'Cadeau du 18',
                'gift_message' => 'Une semaine avant Noël, ça se fête !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_GAME,
                'challenge_data' => $game(
                    'memory',
                    'Mémoire du 19 décembre',
                    'Dernier défi de mémoire avant les fêtes. Concentration !',
                    ['🎄', '🥂', '🌟', '🧣', '🔔', '🍫'],
                ),
                'gift_title' => 'Le brunch de dimanche',
                'gift_message' => 'Prêt(e) pour Noël, c\'est certain.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Petite boule qui brille, fragile mais précieuse, on m\'accroche au sapin. Suis-je ?',
                    ['boule', 'une boule', 'la boule'],
                ),
                'gift_title' => 'Le cadeau du 20',
                'gift_message' => 'Plus que 4 jours !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_QCM,
                'challenge_data' => $qcm(
                    'Que représentent traditionnellement les bougies de la couronne de l\'Avent ?',
                    ['Les 4 semaines avant Noël', 'Les 4 éléments', 'Les 4 saisons'],
                    0,
                ),
                'gift_title' => 'La couronne du 21',
                'gift_message' => 'Une semaine avant le grand soir.',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Il tombe du ciel en hiver, il fait craquer les pas et tout blanchit. Qu\'est-ce ?',
                    ['neige', 'la neige', 'flocon', 'un flocon'],
                ),
                'gift_title' => 'Cadeau du 22',
                'gift_message' => 'Dernière ligne droite !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'J\'annonce la nouvelle et je sonne au clocher le soir de Noël. Suis-je ?',
                    ['cloche', 'une cloche', 'les cloches'],
                ),
                'gift_title' => 'L\'avant-première',
                'gift_message' => 'Deux jours avant Noël : l\'excitation monte !',
            ],
            [
                'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
                'challenge_data' => $riddle(
                    'Il apporte les cadeaux, les sourires et une nuit magique. Qui est-ce ?',
                    ['père noel', 'papa noel', 'santa', 'santa claus', 'saint nicolas'],
                ),
                'gift_title' => 'Le grand cadeau de Noël 🎁',
                'gift_message' => 'Joyeux Noël ! Merci d\'être toi.',
            ],
        ];

        foreach ($days as $index => $day) {
            CalendarDay::create([
                ...$day,
                'day' => $index + 1,
                'available_at' => Carbon::createFromDate(null, 12, 1)->addDays($index),
            ]);
        }
    }
}
