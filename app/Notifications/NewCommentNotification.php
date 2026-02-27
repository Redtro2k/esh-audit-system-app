<?php

namespace App\Notifications;

use App\Filament\Resources\Observations\ObservationResource;
use App\Models\Observation;
use Filament\Actions\Action as FilamentNotificationAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected mixed $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return config('commentions.notifications.mentions.channels', ['database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject((string) config('commentions.notifications.mentions.mail.subject', 'You were mentioned in a comment'))
            ->line('You were mentioned in a comment.')
            ->line($this->commentExcerpt())
            ->action('Open observation', $this->commentUrl());
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('You were mentioned in a comment')
            ->body($this->commentExcerpt())
            ->actions([
                FilamentNotificationAction::make('open')
                    ->label('Open')
                    ->url($this->commentUrl()),
            ])
            ->getDatabaseMessage();
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    protected function commentExcerpt(): string
    {
        $body = (string) (
            data_get($this->comment, 'comment')
            ?? data_get($this->comment, 'content')
            ?? ''
        );

        if ($body === '') {
            return 'Open the app to view the new mention.';
        }

        return mb_strimwidth($body, 0, 140, '...');
    }

    protected function commentUrl(): string
    {
        $commentableType = (string) data_get($this->comment, 'commentable_type', '');
        $commentableId = data_get($this->comment, 'commentable_id');

        if ($commentableType === \App\Models\Observation::class && $commentableId) {
            $observation = Observation::query()->find($commentableId);

            if (! $observation) {
                return ObservationResource::getUrl('index');
            }

            if (! ObservationResource::canView($observation)) {
                return ObservationResource::getUrl('index');
            }

            return ObservationResource::getUrl('view', ['record' => $observation]);
        }

        return url('/admin');
    }
}
