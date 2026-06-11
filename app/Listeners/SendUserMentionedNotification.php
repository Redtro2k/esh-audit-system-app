<?php

namespace App\Listeners;

use Filament\Notifications\Events\DatabaseNotificationsSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Kirschbaum\Commentions\Events\UserWasMentionedEvent;
use Kirschbaum\Commentions\Notifications\UserMentionedInComment;

class SendUserMentionedNotification
{
    public function handle(UserWasMentionedEvent $event): void
    {
        if (! config('commentions.notifications.mentions.enabled', false)) {
            return;
        }

        $channels = (array) config('commentions.notifications.mentions.channels', []);

        if (empty($channels)) {
            return;
        }

        if (
            in_array('database', $channels, true)
            && $event->user->notifications()
                ->where('type', (string) config('commentions.notifications.mentions.notification', UserMentionedInComment::class))
                ->where('data->comment_id', $event->comment->getId())
                ->exists()
        ) {
            return;
        }

        $mentionedUsers = $event->comment
            ->getMentioned()
            ->map(fn ($user) => [
                'id' => $user->getKey(),
                'name' => data_get($user, 'name'),
                'email' => data_get($user, 'email'),
            ])
            ->values()
            ->all();
        $firstMentionedUserId = data_get($mentionedUsers, '0.id');

        // Event is dispatched per mentioned user; log once using the first one.
        if ((string) $event->user->getKey() === (string) $firstMentionedUserId) {
            Log::info('Comment mention detected', [
                'comment_id' => $event->comment->getId(),
                'author_id' => data_get($event->comment, 'author_id'),
                'commentable_type' => data_get($event->comment, 'commentable_type'),
                'commentable_id' => data_get($event->comment, 'commentable_id'),
                'mentioned_users' => $mentionedUsers,
            ]);
        }

        $notificationClass = (string) config('commentions.notifications.mentions.notification', UserMentionedInComment::class);
        $notification = app($notificationClass, ['comment' => $event->comment, 'channels' => $channels]);

        Notification::send($event->user, $notification);

        if (in_array('database', $channels, true)) {
            DatabaseNotificationsSent::dispatch($event->user);
        }
    }
}
