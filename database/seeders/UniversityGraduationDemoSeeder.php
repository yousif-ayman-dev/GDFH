<?php

namespace Database\Seeders;

use App\Models\AIConversation;
use App\Models\AIMessage;
use App\Models\Contract;
use App\Models\Conversation;
use App\Models\FreelancerProfile;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\Service;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Worklog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UniversityGraduationDemoSeeder extends Seeder
{
    /**
     * Seed realistic demo data for University Graduation Project presentation.
     */
    public function run(): void
    {
        // 1. Create Key Demo Users

        // Admin (System Administrator)
        $admin = User::firstOrCreate([
            'email' => 'admin@gdfh.edu',
        ], [
            'name' => 'مدير النظام (System Administrator)',
            'username' => 'system_admin',
            'password' => bcrypt('password'),
            'account_type' => 'client',
            'onboarded_at' => now(),
            'is_admin' => true,
        ]);
        // Ensure existing admin record has is_admin set
        if (! $admin->is_admin) {
            $admin->update(['is_admin' => true]);
        }

        $client = User::firstOrCreate([
            'email' => 'client@gdfh.edu',
        ], [
            'name' => 'د. أحمد محمود (شركة الابتكار الرقمي)',
            'username' => 'dr_ahmed',
            'password' => bcrypt('password'),
            'account_type' => 'client',
            'onboarded_at' => now(),
        ]);

        $freelancer1 = User::firstOrCreate([
            'email' => 'freelancer1@gdfh.edu',
        ], [
            'name' => 'يوسف أيمن (مطور لاراڤيل خبير)',
            'username' => 'yousif_dev',
            'password' => bcrypt('password'),
            'account_type' => 'freelancer',
            'onboarded_at' => now(),
        ]);

        $freelancer2 = User::firstOrCreate([
            'email' => 'freelancer2@gdfh.edu',
        ], [
            'name' => 'سارة علي (مصممة واجهات UI/UX)',
            'username' => 'sara_design',
            'password' => bcrypt('password'),
            'account_type' => 'freelancer',
            'onboarded_at' => now(),
        ]);

        // 2. Create Freelancer Profiles
        FreelancerProfile::updateOrCreate(['user_id' => $freelancer1->id], [
            'title' => 'مطور أنظمة لاراڤيل وخبير قواعد البيانات',
            'bio' => 'مهندس برمجيات متخصص في بناء المنصات المؤسسية وتطوير واجهات البرمجة RESTful APIs بالاعتماد على Laravel 12 و Livewire و TailwindCSS.',
            'hourly_rate' => 50.00,
            'skills' => ['Laravel', 'PHP', 'Vue.js', 'MySQL', 'TailwindCSS'],
            'rating' => 4.95,
            'reviews_count' => 12,
            'completed_projects_count' => 15,
            'availability' => 'available',
        ]);

        FreelancerProfile::updateOrCreate(['user_id' => $freelancer2->id], [
            'title' => 'أخصائية تصميم واجهات وتجربة المستخدم',
            'bio' => 'خبيرة في تصميم الأنظمة المعقدة والتطبيقات التفاعلية مع التركيز على سهولة الاستخدام وتجربة المستخدم البصرية الجذابة.',
            'hourly_rate' => 40.00,
            'skills' => ['Figma', 'UI/UX', 'CSS3', 'Design Systems'],
            'rating' => 5.00,
            'reviews_count' => 8,
            'completed_projects_count' => 10,
            'availability' => 'available',
        ]);

        // 3. Create Services Catalog
        Service::firstOrCreate(['slug' => 'laravel-enterprise-backend-service'], [
            'user_id' => $freelancer1->id,
            'title' => 'تطوير خلفية موقع مؤسسي متكامل بلاراڤيل 12',
            'description' => 'بناء وبنية أنظمة خلفية آمنة وسريعة مع حماية البيانات وإدارة الصلاحيات والتنبيهات المباشرة.',
            'price' => 450.00,
            'delivery_days' => 5,
            'category' => 'تطوير البرمجيات',
            'status' => 'active',
            'rating' => 5.00,
        ]);

        Service::firstOrCreate(['slug' => 'ui-ux-design-system-service'], [
            'user_id' => $freelancer2->id,
            'title' => 'تصميم نظام واجهات وتجربة مستخدم الاحترافي',
            'description' => 'تصميم لوحة تحكم عصرية بالنمط الداكن والفاتح شاملة الكومبوننت والتفاعلات مع مخرجات Figma كاملة.',
            'price' => 300.00,
            'delivery_days' => 4,
            'category' => 'التصميم والإبداع',
            'status' => 'active',
            'rating' => 4.90,
        ]);

        // 4. Create Team & Members
        $team = Team::firstOrCreate(['slug' => 'tasker-core-engineering-team'], [
            'owner_id' => $client->id,
            'name' => 'فريق هندسة منصة Tasker',
            'description' => 'فريق التطوير البرمجي وإدارة الجودة لمنصة Tasker.',
        ]);

        TeamMember::firstOrCreate(['team_id' => $team->id, 'user_id' => $freelancer1->id], [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        TeamMember::firstOrCreate(['team_id' => $team->id, 'user_id' => $freelancer2->id], [
            'role' => 'admin',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // 5. Create Demo Projects
        $projectActive = Project::firstOrCreate(['slug' => 'tasker-university-graduation-platform'], [
            'owner_id' => $client->id,
            'title' => 'مشروع التخرج: منصة Tasker لإدارة المشاريع وسوق العمل الذكي',
            'description' => 'نظام مؤسسي شامل لإدارة المشاريع، اللوحات الزمانية Kanban & Gantt، تتبع الوقت، ومساعد الذكاء الاصطناعي مع سوق الخدمات المصغرة.',
            'visibility' => 'private',
            'status' => 'in_progress',
            'budget_min' => 1500.00,
            'budget_max' => 2500.00,
            'currency' => 'USD',
            'start_date' => now()->subDays(15),
            'deadline' => now()->addDays(15),
        ]);

        $projectCompleted = Project::firstOrCreate(['slug' => 'university-library-portal'], [
            'owner_id' => $client->id,
            'title' => 'بوابة المكتبة الجامعية الذكية',
            'description' => 'تطبيق ويب لتنظيم وإعارة الكتب والأبحاث العلمية للطلاب والأساتذة.',
            'visibility' => 'private',
            'status' => 'completed',
            'budget_min' => 800.00,
            'budget_max' => 1200.00,
            'currency' => 'USD',
            'start_date' => now()->subMonths(2),
            'deadline' => now()->subMonth(),
        ]);

        // Link project members
        ProjectMember::firstOrCreate(['project_id' => $projectActive->id, 'user_id' => $freelancer1->id], [
            'role' => 'project_manager',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // 6. Create Tasks
        $task1 = Task::firstOrCreate([
            'project_id' => $projectActive->id,
            'title' => 'تطوير محرك تتبع الوقت والعمليات الزمنية Worklogs',
        ], [
            'created_by' => $freelancer1->id,
            'assigned_to' => $freelancer1->id,
            'description' => 'إنشاء جداول وواجهات تتبع الوقت مع حساب الساعات الإجمالية وتقارير الانتاجية.',
            'status' => 'completed',
            'priority' => 'high',
            'due_at' => now()->subDays(2),
        ]);

        $task2 = Task::firstOrCreate([
            'project_id' => $projectActive->id,
            'title' => 'إعداد مساعد الذكاء الاصطناعي التفاعلي للجامعة',
        ], [
            'created_by' => $client->id,
            'assigned_to' => $freelancer1->id,
            'description' => 'تطوير المزود القائم على القواعد Rule-Based AI لحساب مؤشر صحة بيئة العمل.',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'due_at' => now()->addDays(3),
        ]);

        // 7. Create Worklog
        Worklog::create([
            'project_id' => $projectActive->id,
            'task_id' => $task1->id,
            'user_id' => $freelancer1->id,
            'start_time' => now()->subHours(4),
            'end_time' => now()->subHours(1),
            'duration' => 10800,
            'status' => 'manual',
            'notes' => 'تطوير محرك Worklog وتحديد الصلاحيات وربطه بالتقارير.',
        ]);

        // 8. Create Proposals & Contracts
        $proposal = Proposal::firstOrCreate([
            'project_id' => $projectActive->id,
            'freelancer_id' => $freelancer1->id,
        ], [
            'bid_amount' => 2200.00,
            'delivery_days' => 20,
            'cover_letter' => 'يسرني تقديم عرض متكامل لتنفيذ منصة GDFH بأعلى معايير الجودة والهندسة البرمجية.',
            'status' => 'accepted',
        ]);

        $contract = Contract::firstOrCreate([
            'project_id' => $projectActive->id,
            'proposal_id' => $proposal->id,
        ], [
            'client_id' => $client->id,
            'freelancer_id' => $freelancer1->id,
            'title' => 'عقد اتفاقية: ' . $projectActive->title,
            'amount' => 2200.00,
            'status' => 'active',
            'start_date' => now()->subDays(10),
        ]);

        // 9. Create Review for Completed Project
        Review::firstOrCreate([
            'project_id' => $projectCompleted->id,
            'reviewer_id' => $client->id,
        ], [
            'reviewee_id' => $freelancer1->id,
            'rating' => 5,
            'comment' => 'عمل ممتاز واحترافي جداً! التزام تام بالمتطلبات والمواعيد المحسوبة.',
            'status' => 'published',
        ]);

        // 10. Create Direct Conversation & Messages
        $conversation = Conversation::firstOrCreate([
            'user_one_id' => min($client->id, $freelancer1->id),
            'user_two_id' => max($client->id, $freelancer1->id),
        ], [
            'last_message_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $client->id,
            'content' => 'مرحباً يوسف، كيف يسير التقديم في المشروع حتى الآن؟',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $freelancer1->id,
            'content' => 'أهلاً بك دكتور أحمد، التقديم يسير وفق المخطط الزمني ونحن في المراحل النهائية لنسخة التخرج.',
        ]);

        // 11. Create AI Assistant Conversation Demo
        $aiConv = AIConversation::create([
            'user_id' => $client->id,
            'title' => 'تحليل صحة مشروع التخرج منصة Tasker',
        ]);

        AIMessage::create([
            'conversation_id' => $aiConv->id,
            'role' => 'user',
            'content' => 'كيف ترى تقييم أداء فريق العمل ومعدل إنجاز المهام هذا الأسبوع؟',
        ]);

        AIMessage::create([
            'conversation_id' => $aiConv->id,
            'role' => 'assistant',
            'content' => "بناءً على تحليل بيانات بيئة العمل:\n- مؤشر صحة المشروع: 92/100 (ممتاز)\n- معدل إنجاز المهام: 85%\n- لا توجد مهام متأخرة صريحة.\nنوصي بمتابعة مرحلة التوثيق النهائية قبل العرض الجامعي.",
        ]);
    }
}
