<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Goal;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\PointTransaction;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if it doesn't exist
        $user = User::firstOrCreate([
            'email' => 'test@example.com'
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);

        // Create a sample goal
        $goal = Goal::create([
            'title' => 'Launch YouTube Channel',
            'description' => 'Create and launch a successful YouTube channel about productivity and personal development.',
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(30),
            'difficulty_level' => 'medium',
            'user_id' => $user->id,
        ]);

        // Create milestones for the goal
        $milestone1 = Milestone::create([
            'goal_id' => $goal->id,
            'title' => 'Content Planning',
            'description' => 'Plan first 10 videos and create content calendar',
            'due_date' => now()->addDays(7),
        ]);

        $milestone2 = Milestone::create([
            'goal_id' => $goal->id,
            'title' => 'Channel Setup',
            'description' => 'Set up YouTube channel with branding and initial videos',
            'due_date' => now()->addDays(21),
        ]);

        // Create tasks
        $tasks = [
            [
                'title' => 'Shoot video for YouTube',
                'description' => 'Record the first video about productivity tips',
                'due_date' => now()->addHours(2),
                'status' => 'pending',
                'points' => 50,
                'milestone_id' => $milestone1->id,
            ],
            [
                'title' => 'Edit video content',
                'description' => 'Edit the recorded video and add graphics',
                'due_date' => now()->subDays(1),
                'status' => 'done',
                'points' => 30,
                'milestone_id' => $milestone1->id,
            ],
            [
                'title' => 'Create thumbnail design',
                'description' => 'Design eye-catching thumbnail for the video',
                'due_date' => now()->addDays(1),
                'status' => 'in_progress',
                'points' => 20,
                'milestone_id' => $milestone1->id,
            ],
            [
                'title' => 'Write video description',
                'description' => 'Write compelling description with SEO keywords',
                'due_date' => now()->addDays(2),
                'status' => 'pending',
                'points' => 15,
                'milestone_id' => $milestone1->id,
            ],
            [
                'title' => 'Set up YouTube channel',
                'description' => 'Create YouTube channel with proper branding',
                'due_date' => now()->addDays(10),
                'status' => 'pending',
                'points' => 40,
                'milestone_id' => $milestone2->id,
            ],
        ];

        foreach ($tasks as $taskData) {
            $task = Task::create([
                'goal_id' => $goal->id,
                'user_id' => $user->id,
                ...$taskData
            ]);

            // Create point transaction for completed tasks
            if ($task->status === 'done') {
                PointTransaction::create([
                    'user_id' => $user->id,
                    'source' => 'task_completion',
                    'amount' => $task->points,
                    'description' => "Completed task: {$task->title}",
                    'created_at' => now()->subDays(rand(1, 6)),
                ]);
            }
        }

        // Create some additional point transactions
        PointTransaction::create([
            'user_id' => $user->id,
            'source' => 'bonus',
            'amount' => 25,
            'description' => 'Weekly streak bonus',
            'created_at' => now()->subDays(2),
        ]);

        PointTransaction::create([
            'user_id' => $user->id,
            'source' => 'streak',
            'amount' => 10,
            'description' => '3-day completion streak',
            'created_at' => now()->subDays(1),
        ]);

        // Create another goal for variety
        $goal2 = Goal::create([
            'title' => 'Learn Laravel Advanced Features',
            'description' => 'Master advanced Laravel concepts and build a complex application.',
            'start_date' => now()->subDays(14),
            'end_date' => now()->addDays(45),
            'difficulty_level' => 'hard',
            'user_id' => $user->id,
        ]);

        // Add some tasks to the second goal
        Task::create([
            'goal_id' => $goal2->id,
            'user_id' => $user->id,
            'title' => 'Study Eloquent Relationships',
            'description' => 'Deep dive into complex Eloquent relationships',
            'due_date' => now()->addDays(3),
            'status' => 'pending',
            'points' => 35,
        ]);

        Task::create([
            'goal_id' => $goal2->id,
            'user_id' => $user->id,
            'title' => 'Build API with Laravel',
            'description' => 'Create a RESTful API using Laravel',
            'due_date' => now()->subDays(2),
            'status' => 'done',
            'points' => 60,
        ]);

        // Point transaction for the completed Laravel task
        PointTransaction::create([
            'user_id' => $user->id,
            'source' => 'task_completion',
            'amount' => 60,
            'description' => 'Completed task: Build API with Laravel',
            'created_at' => now()->subDays(2),
        ]);
    }
}
