<?php

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

class TemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Template $template): bool
    {
        return $user->is_admin || $template->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('templates.create');
    }

    public function update(User $user, Template $template): bool
    {
        return $user->hasPermission('templates.update');
    }

    public function delete(User $user, Template $template): bool
    {
        return $user->hasPermission('templates.delete');
    }

    public function apply(User $user, Template $template): bool
    {
        return $this->view($user, $template);
    }
}
