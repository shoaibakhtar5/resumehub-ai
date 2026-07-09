<?php

namespace App\Policies;

use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Resume $resume): bool
    {
        return $user->is_admin || $resume->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, Resume $resume): bool
    {
        return $this->view($user, $resume);
    }

    public function delete(User $user, Resume $resume): bool
    {
        return $this->view($user, $resume);
    }

    public function restore(User $user, Resume $resume): bool
    {
        return $this->view($user, $resume);
    }

    public function share(User $user, Resume $resume): bool
    {
        return $this->view($user, $resume);
    }

    public function download(User $user, Resume $resume): bool
    {
        return $this->view($user, $resume);
    }
}
