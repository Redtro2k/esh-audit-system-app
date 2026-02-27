<?php

namespace App\Observers;

use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Comment;

class CommentObserver
{
    /**
     * @var array<int, array<int|string>>
     */
    protected array $removedMentionIdsByCommentId = [];

    public function updating(Comment $comment): void
    {
        $previousMentionIds = $this->extractMentionIds((string) $comment->getOriginal('body'));
        $currentMentionIds = $this->extractMentionIds((string) $comment->body);

        $removed = $previousMentionIds->diff($currentMentionIds)->values()->all();

        if (empty($removed)) {
            return;
        }

        $this->removedMentionIdsByCommentId[(int) $comment->getKey()] = $removed;
    }

    public function updated(Comment $comment): void
    {
        $commentId = (int) $comment->getKey();
        $removedMentionIds = $this->removedMentionIdsByCommentId[$commentId] ?? [];

        if (empty($removedMentionIds)) {
            return;
        }

        unset($this->removedMentionIdsByCommentId[$commentId]);

        $commentable = $comment->commentable;

        if (! $commentable || ! method_exists($commentable, 'unsubscribe')) {
            return;
        }

        $commenterModel = (string) config('commentions.commenter.model', \App\Models\User::class);
        $removedUsers = $commenterModel::query()
            ->whereKey($removedMentionIds)
            ->get();

        foreach ($removedUsers as $user) {
            if ($this->isUserMentionedAnywhere($commentable->comments()->get(), $user->getKey())) {
                continue;
            }

            $commentable->unsubscribe($user);
        }
    }

    protected function extractMentionIds(string $body): Collection
    {
        preg_match_all(
            '/<span[^>]*data-type="mention"[^>]*data-id="(\d+)"[^>]*>/',
            $body,
            $matches
        );

        return collect($matches[1] ?? [])
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();
    }

    protected function isUserMentionedAnywhere(Collection $comments, int|string $userId): bool
    {
        $userId = (string) $userId;

        foreach ($comments as $comment) {
            if ($this->extractMentionIds((string) $comment->body)->contains($userId)) {
                return true;
            }
        }

        return false;
    }
}

