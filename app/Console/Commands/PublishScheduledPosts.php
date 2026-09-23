<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled {--force : تجاهل قاعدة المقال الواحد في الإصدار}';

    protected $description = 'نشر المقالات المجدولة التي حل موعد نشرها (الإصدار اليومي)';

    public function handle(): int
    {
        $due = Post::where('status', 'scheduled')
            ->where('published_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('لا توجد مقالات مجدولة مستحقة الآن.');

            return self::SUCCESS;
        }

        foreach ($due as $post) {
            // الإصدار اليومي: مقال واحد لكل يوم لكل قسم (الأولوية للمميز ثم الأقدم)
            $alreadyPublishedToday = Post::published()
                ->whereDate('published_at', now()->toDateString())
                ->exists();

            if ($alreadyPublishedToday && !$this->option('force')) {
                $this->warn("تأجيل «{$post->title}» — نُشر مقال آخر اليوم (قاعدة الإصدار اليومي).");

                continue;
            }

            $post->status = 'published';
            $post->save();

            $this->info("نُشر: {$post->title}");
        }

        return self::SUCCESS;
    }
}