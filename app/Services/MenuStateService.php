<?php

namespace App\Services;

use App\Models\Menu;
use Exception;

class MenuStateService {
    protected $transitions = [
        'draft' => ['pending_review'],
        'pending_review' => ['under_review', 'rejected'],
        'under_review' => ['approved', 'needs_revision', 'rejected'],
        'needs_revision' => ['pending_review'],
        'approved' => [], // No transitions from 'approved'
        'rejected' => [], // No transitions from 'rejected'
    ];

    public function canTransition(Menu $menu, string $toState): bool {
        return in_array($toState, $this->transitions[$menu->status] ?? []);
    }

    public function transition(Menu $menu, string $toState): bool {
        if (!$this->canTransition($menu, $toState)) {
            throw new Exception("Invalid transition from {$menu->status} to $toState.");
        }

        $menu->status = $toState;
        $menu->save();
        
        // Optionally, dispatch events or perform additional actions here

        return true;
    }
}
