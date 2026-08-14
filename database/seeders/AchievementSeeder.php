<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'slug' => 'first_word',
                'name' => 'Primeira Palavra',
                'description' => 'Formou sua primeira palavra válida.',
                'icon' => '📝',
                'category' => 'words',
                'target_value' => 1,
                'xp_reward' => 10,
            ],
            [
                'slug' => 'words_100',
                'name' => 'Vocabulário Rico',
                'description' => 'Formou 100 palavras válidas.',
                'icon' => '📚',
                'category' => 'words',
                'target_value' => 100,
                'xp_reward' => 50,
            ],
            [
                'slug' => 'words_500',
                'name' => 'Mestre das Palavras',
                'description' => 'Formou 500 palavras válidas.',
                'icon' => '🎓',
                'category' => 'words',
                'target_value' => 500,
                'xp_reward' => 100,
            ],
            [
                'slug' => 'first_win',
                'name' => 'Primeira Vitória',
                'description' => 'Venceu sua primeira partida.',
                'icon' => '🏆',
                'category' => 'games',
                'target_value' => 1,
                'xp_reward' => 20,
            ],
            [
                'slug' => 'wins_10',
                'name' => 'Competidor',
                'description' => 'Venceu 10 partidas.',
                'icon' => '⭐',
                'category' => 'games',
                'target_value' => 10,
                'xp_reward' => 100,
            ],
            [
                'slug' => 'wins_50',
                'name' => 'Campeão',
                'description' => 'Venceu 50 partidas.',
                'icon' => '👑',
                'category' => 'games',
                'target_value' => 50,
                'xp_reward' => 250,
            ],
            [
                'slug' => 'combo_5',
                'name' => 'Sequência',
                'description' => 'Conseguiu um combo de 5 palavras.',
                'icon' => '🔥',
                'category' => 'combos',
                'target_value' => 5,
                'xp_reward' => 30,
            ],
            [
                'slug' => 'combo_10',
                'name' => 'Imparável',
                'description' => 'Conseguiu um combo de 10 palavras.',
                'icon' => '💥',
                'category' => 'combos',
                'target_value' => 10,
                'xp_reward' => 75,
            ],
            [
                'slug' => 'perfect_word',
                'name' => 'Palavra Perfeita',
                'description' => 'Usou todas as letras disponíveis em uma palavra.',
                'icon' => '💎',
                'category' => 'special',
                'target_value' => 1,
                'xp_reward' => 50,
            ],
            [
                'slug' => 'score_100',
                'name' => 'Centenário',
                'description' => 'Fez mais de 100 pontos em uma partida.',
                'icon' => '💯',
                'category' => 'special',
                'target_value' => 1,
                'xp_reward' => 40,
            ],
            [
                'slug' => 'long_word_7',
                'name' => 'Palavrão',
                'description' => 'Formou uma palavra com 7 ou mais letras.',
                'icon' => '📏',
                'category' => 'words',
                'target_value' => 1,
                'xp_reward' => 25,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }
    }
}
