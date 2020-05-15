<?php

namespace App;

trait HasStatistics
{
    public function statistics()
    {
        return collect([
            'tasks' => [
                'billed_at'    => $this->billedStatistics(),
                'completed_at' => $this->completedStatistics(),
                'created_at'   => $this->createdStatistics()
            ]
        ]);
    }

    private function billedStatistics()
    {
        return $this->tasks()
            ->whereNotNull('billed_at')
            ->latest()
            ->get()
            ->groupBy(function ($task) {
                return $task->billed_at->format('W');
            })
            ->take(10)->map(function ($item, $key) {
                return $item->count();
            });
    }

    private function completedStatistics()
    {
        return $this->tasks()
            ->whereNotNull('completed_at')
            ->latest()
            ->get()
            ->groupBy(function ($task) {
                return $task->completed_at->format('W');
            })
            ->take(10)->map(function ($item, $key) {
                return $item->count();
            });
    }

    private function createdStatistics()
    {
        return $this->tasks()
            ->latest()
            ->get()
            ->groupBy(function ($task) {
                return $task->created_at->format('W');
            })
            ->take(10)->map(function ($item, $key) {
                return $item->count();
            });
    }
}