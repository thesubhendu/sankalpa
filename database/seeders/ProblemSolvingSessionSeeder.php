<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProblemSolvingSession;
use App\Models\User;

class ProblemSolvingSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $sessions = [
            [
                'title' => 'Procrastination on Important Tasks',
                'problem_description' => 'I keep putting off important work tasks until the last minute, which creates stress and reduces quality of work.',
                'what_doing_wrong' => 'I scroll social media and watch YouTube videos instead of starting my work tasks.',
                'trigger' => 'When I feel overwhelmed by the size or complexity of a task, I immediately reach for my phone.',
                'is_daily_pattern' => true,
                'what_to_change_trigger' => 'Break down large tasks into smaller chunks, put phone in another room, use website blockers.',
                'what_do_when_doing_wrong' => 'I convince myself I\'ll just watch one video, then end up watching for hours.',
                'long_term_impact' => 'This pattern is damaging my career prospects, causing missed deadlines, and creating chronic stress.',
                'what_should_do_instead' => 'Start with the smallest possible version of the task, work in 25-minute focused blocks.',
                'how_would_benefit' => 'I would feel more accomplished, reduce stress, produce better quality work, and advance my career.',
                'problem_nature' => 'work',
                'emotional_impact_percentage' => 70,
                'emotional_strategy' => 'I use deep breathing when I feel overwhelmed, and try to practice self-compassion instead of self-criticism.',
                'have_power_to_solve' => true,
                'what_have_power_to_change' => 'My environment setup, my response to overwhelming feelings, my task breakdown process.',
                'how_get_out_long_term' => 'By consistently applying the small-chunk method and environmental changes, I can build new neural pathways that default to focused work.',
                'status' => 'in_progress',
                'tags' => ['productivity', 'procrastination', 'work'],
                'needs_follow_up' => true,
                'session_date' => now()->subDays(2),
            ],
            [
                'title' => 'Getting Defensive in Relationships',
                'problem_description' => 'When my partner gives me feedback, I immediately get defensive and turn it into an argument.',
                'what_doing_wrong' => 'I interrupt, make excuses, and counter-attack instead of listening to understand.',
                'trigger' => 'Any criticism or feedback that feels like an attack on my character or competence.',
                'is_daily_pattern' => false,
                'what_to_change_trigger' => 'Practice mindfulness to pause before responding, remind myself that feedback is not an attack.',
                'what_do_when_doing_wrong' => 'I raise my voice, bring up past issues, and try to prove the other person wrong.',
                'long_term_impact' => 'This is damaging my closest relationships and preventing me from growing as a person.',
                'what_should_do_instead' => 'Take a deep breath, ask clarifying questions, thank them for the feedback, then process it.',
                'how_would_benefit' => 'Stronger relationships, better personal growth, less conflict and stress at home.',
                'problem_nature' => 'relationships',
                'emotional_impact_percentage' => 60,
                'emotional_strategy' => 'I practice meditation daily and work on recognizing my emotional triggers before they escalate.',
                'have_power_to_solve' => true,
                'what_have_power_to_change' => 'My initial response, my breathing, my interpretation of feedback as care rather than attack.',
                'how_get_out_long_term' => 'By practicing the pause-and-breathe technique consistently, I can rewire my automatic defensive response.',
                'status' => 'draft',
                'tags' => ['relationships', 'communication', 'defensiveness'],
                'needs_follow_up' => true,
                'session_date' => now()->subDays(1),
            ],
            [
                'title' => 'Emotional Eating When Stressed',
                'problem_description' => 'When I feel stressed or anxious, I automatically reach for junk food and eat mindlessly.',
                'what_doing_wrong' => 'Using food as an emotional coping mechanism instead of dealing with the underlying stress.',
                'trigger' => 'Work stress, relationship conflicts, financial worries, or feeling overwhelmed.',
                'is_daily_pattern' => true,
                'what_to_change_trigger' => 'Keep healthier snacks available, practice stress management techniques, identify stress early.',
                'what_do_when_doing_wrong' => 'I eat quickly without tasting, often while distracted, and feel guilty afterward.',
                'long_term_impact' => 'Weight gain, poor health, guilt cycles, and not addressing the root causes of stress.',
                'what_should_do_instead' => 'Practice mindful breathing, go for a walk, call a friend, or do 5 minutes of journaling.',
                'how_would_benefit' => 'Better physical health, improved stress management skills, breaking the guilt cycle.',
                'problem_nature' => 'emotional',
                'emotional_impact_percentage' => 50,
                'emotional_strategy' => 'I practice mindful eating techniques and keep a stress journal to identify patterns.',
                'have_power_to_solve' => true,
                'what_have_power_to_change' => 'My environment (food availability), my stress response habits, my awareness of triggers.',
                'how_get_out_long_term' => 'By building alternative stress-relief habits and addressing stress at its source, I can break this cycle.',
                'status' => 'completed',
                'tags' => ['health', 'stress', 'emotional-eating'],
                'needs_follow_up' => false,
                'session_date' => now()->subWeek(),
            ],
        ];

        foreach ($sessions as $sessionData) {
            ProblemSolvingSession::create(array_merge($sessionData, ['user_id' => $user->id]));
        }

        $this->command->info('Problem-solving sessions seeded successfully!');
    }
}
