<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IntrospectionJournal;
use App\Models\User;
use Carbon\Carbon;

class IntrospectionJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // Get the first user

        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }

        // Sample Data Drop entries
        $dataDrops = [
            [
                'title' => 'Social Media Reaction Anxiety',
                'content' => 'Posted on Facebook about my birthday and started obsessing over who liked/reacted and who ignored it. This triggered feelings of insecurity and need for validation.',
                'what_happened' => 'Posted birthday message on Facebook, watched for reactions throughout the day',
                'feelings' => 'Anxious, insecure, needy for validation, disappointed when certain people didn\'t react',
                'trigger_reason' => 'Fear of rejection and need for social approval. Ego attachment to how others perceive me.',
                'mood' => 'low',
                'trigger' => 'Social media validation seeking',
                'intensity_level' => 6,
                'tags' => ['social_media', 'validation', 'ego', 'birthday'],
                'is_important' => true,
                'needs_review' => true,
            ],
            [
                'title' => 'Decision Paralysis on Job Application',
                'content' => 'Spent 3 hours researching a job application, overthinking every detail, and then got exhausted without applying. This pattern keeps repeating.',
                'what_happened' => 'Found interesting job posting, started researching company extensively, got overwhelmed',
                'feelings' => 'Overwhelmed, frustrated with myself, paralyzed by options',
                'trigger_reason' => 'Perfectionism and fear of making wrong choice. Want everything to be perfect before taking action.',
                'mood' => 'low',
                'trigger' => 'Job application decision',
                'intensity_level' => 7,
                'tags' => ['decision_paralysis', 'perfectionism', 'career'],
                'is_important' => true,
                'needs_review' => true,
            ],
            [
                'title' => 'Comfort Zone Resistance',
                'content' => 'Avoided going to the bank again today. Made excuses about being busy but reality is I just don\'t want to deal with the bureaucracy and potential complications.',
                'what_happened' => 'Had to go to bank for account update, kept postponing it',
                'feelings' => 'Resistant, lazy, avoiding responsibility',
                'trigger_reason' => 'Comfort zone attachment and aversion to dealing with system complexities.',
                'mood' => 'neutral',
                'trigger' => 'Banking responsibility',
                'intensity_level' => 4,
                'tags' => ['comfort_zone', 'responsibility', 'avoidance'],
                'is_important' => false,
                'needs_review' => true,
            ],
        ];

        // Sample Learning entries
        $learnings = [
            [
                'title' => 'Pattern: Greed for Money Disturbs Peace',
                'content' => '**Key Learning:** When I focus too much on money and financial outcomes, I lose my mental peace and clarity. Money becomes the master instead of being a tool.\n\n**What I noticed:**\n- Checking investment portfolio obsessively\n- Making decisions based on fear of loss rather than wisdom\n- Losing sleep over market movements\n\n**Action:** Need to remember that financial security comes from inner stability, not external accumulation.',
                'mood' => 'neutral',
                'tags' => ['money', 'greed', 'mental_peace', 'pattern'],
                'is_important' => true,
            ],
            [
                'title' => 'Anger Gives Others Power Over Me',
                'content' => '**Realization:** When I get angry at others, I am literally giving them the power to control my emotional state.\n\n**Why this matters:**\n- I am the master of myself, why give that control to others?\n- Anger stresses my heart and vital organs\n- Others can trigger me because I allow them to\n\n**New approach:** Stay centered in my own power, respond rather than react.',
                'mood' => 'good',
                'tags' => ['anger', 'emotional_control', 'self_mastery'],
                'is_important' => true,
            ],
        ];

        // Sample Rules entries
        $rules = [
            [
                'title' => 'I Am A Warrior - Fear Nothing',
                'content' => '**DAILY REMINDER:** You are a warrior and can handle any situation. Do not be afraid.\n\n**When fear arises, remember:**\n- You are not the body, not the mind\n- You are indestructible Atman\n- Slight discomfort is just sensation, not reality\n- Every challenge is an opportunity to grow stronger',
                'mood' => 'very_good',
                'tags' => ['warrior_mindset', 'fear', 'spiritual', 'strength'],
                'is_important' => true,
            ],
            [
                'title' => 'Embrace Discomfort for Growth',
                'content' => '**RULE:** The things that make you feel uncomfortable are exactly the things you should do to grow.\n\n**Application:**\n- Go to bank when you don\'t want to\n- Apply for jobs even when overwhelmed\n- Take on responsibilities even when scared\n- Step out of comfort zone daily\n\n**Remember:** Comfort zone is the enemy of growth.',
                'mood' => 'good',
                'tags' => ['growth', 'discomfort', 'comfort_zone', 'responsibility'],
                'is_important' => true,
            ],
        ];

        // Sample Purpose entries
        $purposes = [
            [
                'title' => 'Spiritual Goal - Third Eye Mastery',
                'content' => '**Ultimate Purpose:** Know the dimension beyond, perfect third-eye meditation, achieve mastery over mind, experience eternal bliss, reach enlightenment.\n\n**Daily Practice:**\n- Meditation minimum 20 minutes\n- Study spiritual texts\n- Practice self-inquiry\n- Maintain inner awareness throughout day',
                'mood' => 'very_good',
                'tags' => ['spirituality', 'meditation', 'enlightenment', 'purpose'],
                'is_important' => true,
            ],
            [
                'title' => 'Career Goal - Work Abroad',
                'content' => '**Goal:** Work and do job abroad in Europe, USA, or Australia by end of 2025.\n\n**Action Steps:**\n- Apply for jobs abroad consistently\n- Stay active on Twitter/X\n- Build strong LinkedIn presence\n- Improve technical skills\n- Network with international developers',
                'mood' => 'good',
                'tags' => ['career', 'international', 'goals', '2025'],
                'is_important' => true,
            ],
        ];

        // Create entries with different dates over the last 30 days
        $entries = [];
        
        // Add data drops
        foreach ($dataDrops as $index => $entry) {
            $entries[] = array_merge($entry, [
                'type' => 'data_drop',
                'user_id' => $user->id,
                'entry_date' => Carbon::now()->subDays(rand(1, 7)),
                'created_at' => Carbon::now()->subDays(rand(1, 7)),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Add learnings
        foreach ($learnings as $index => $entry) {
            $entries[] = array_merge($entry, [
                'type' => 'learning',
                'user_id' => $user->id,
                'entry_date' => Carbon::now()->subDays(rand(8, 15)),
                'created_at' => Carbon::now()->subDays(rand(8, 15)),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Add rules
        foreach ($rules as $index => $entry) {
            $entries[] = array_merge($entry, [
                'type' => 'rule',
                'user_id' => $user->id,
                'entry_date' => Carbon::now()->subDays(rand(16, 25)),
                'created_at' => Carbon::now()->subDays(rand(16, 25)),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Add purposes
        foreach ($purposes as $index => $entry) {
            $entries[] = array_merge($entry, [
                'type' => 'purpose',
                'user_id' => $user->id,
                'entry_date' => Carbon::now()->subDays(rand(26, 30)),
                'created_at' => Carbon::now()->subDays(rand(26, 30)),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Insert all entries
        foreach ($entries as $entry) {
            IntrospectionJournal::create($entry);
        }

        $this->command->info('Created ' . count($entries) . ' sample introspection journal entries.');
    }
}
