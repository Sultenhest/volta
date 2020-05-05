<?php

namespace App;

use Illuminate\Support\Arr;

trait RecordsActivity
{
    public $oldAttributes = [];

    public static function bootRecordsActivity()
    {
        foreach (self::recordableEvents() as $event) {
            static::$event(function($model) use ($event) {
                $model->recordActivity($model->activityDescription($event));
            });

            if ($event === 'updated') {
                static::updating(function ($model) {
                    $model->oldAttributes = $model->getOriginal();
                });
            }
        }
    }

    protected static function recordableEvents()
    {
        if (isset(static::$recordableEvents)) {
            return static::$recordableEvents;
        }

        return ['created', 'updated'];
    }

    public function recordActivity($description)
    {
        $this->activity()->create([
            'user_id'     => ($this->project ?? $this)->user->id,
            'project_id'  => class_basename($this) === 'Project' ? $this->id : $this->project_id,
            'description' => $description,
            'changes'     => $this->activityChanges()
        ]);
    }

    protected function activityChanges()
    {
        if ($this->wasChanged()) {
            return [
                'before' => Arr::except(array_diff($this->oldAttributes, $this->getAttributes()), 'updated_at'),
                'after'  => Arr::except($this->getChanges(), 'updated_at')
            ];
        }
    }

    protected function activityDescription($description)
    {
        return "{$description}_" . strtolower(class_basename($this));
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject')->latest();
    }
}