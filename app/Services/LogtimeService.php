<?php

namespace App\Services;

use App\Exports\LogtimeMultiSheetExport;
use App\Models\Logtime;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LogtimeService
{
    public function getLogtimes(array $query): LengthAwarePaginator
    {
        $auth = Auth::user();
        $page = $query['page'] ?? 1;

        $targetUserId = (isset($query['user_id']) && in_array($auth->role, ['manager', 'communicator'], true))
            ? $query['user_id']
            : $auth->id;

        $logtimeVersion = Cache::get('logtimes_cache_version', 1);
        $from = $query['from'] ?? 'start';
        $to = $query['to'] ?? 'end';

        $logtimeKey = "all_logtime_lt{$logtimeVersion}_u{$targetUserId}_from{$from}_to{$to}_pg{$page}";

        return Cache::remember($logtimeKey, 1800, function () use ($query, $targetUserId) {
            $logtimesQuery = Logtime::with('task')
                ->where('user_id', $targetUserId)
                ->orderBy('date', 'desc');

            if (isset($query['from'], $query['to'])) {
                $fromDate = Carbon::parse($query['from'])->startOfDay();
                $toDate = Carbon::parse($query['to'])->endOfDay();
                $logtimesQuery->whereBetween('date', [$fromDate, $toDate]);
            }

            return $logtimesQuery->paginate(10)->withQueryString();
        });
    }

    public function getTasksForAuthUser(): Collection
    {
        $auth = Auth::user();
        $tasksVersion = Cache::get('tasks_cache_version', 1);
        $tasksKey = "all_task_t{$tasksVersion}_user{$auth->id}_role{$auth->role}";

        return Cache::remember($tasksKey, 1800, function () use ($auth) {
            $tasksQuery = Task::query()->with('project');

            if ($auth->role === 'engineer') {
                $tasksQuery->where(function ($q) use ($auth) {
                    $q->whereJsonContains('engineer', $auth->id)
                      ->orWhereJsonContains('reviewer', $auth->id);
                });
            } elseif ($auth->role === 'designer') {
                $tasksQuery->whereJsonContains('designer', $auth->id);
            }

            return $tasksQuery->get();
        });
    }

    public function getUsers(): Collection
    {
        $userVersion = Cache::get('users_cache_version', 1);

        return Cache::remember("all_user_u{$userVersion}", 1800, function () {
            return User::orderBy('name')->get();
        });
    }

    public function storeLogtime(array $data): Logtime
    {
        $user = Auth::user();
        $timeUsed = $data['time_used'] < 0 ? 1 : $data['time_used'];
        $task = Task::findOrFail($data['task_id']);

        $logtime = $task->logtimes()->create([
            'user_id'     => $user->id,
            'date'        => Carbon::parse($data['date']),
            'time_used'   => $timeUsed,
            'description' => $data['description'] ?? null,
        ]);

        $this->createLog("[CREATE] logtime {$task->ticket_link} for {$logtime->time_used} hours on {$logtime->date}");

        return $logtime;
    }

    public function updateLogtime(int|string $id, array $data): Logtime
    {
        $logtime = Logtime::with('task')->findOrFail($id);

        $oldTaskTicket = $logtime->task->ticket_link ?? 'Unknown Task';
        $oldTaskId = $logtime->task_id;
        $oldDate = $logtime->date ? Carbon::parse($logtime->date)->format('Y-m-d') : null;
        $oldTimeUsed = (float) $logtime->time_used;
        $oldDescription = $logtime->description ?? 'None';

        $newTask = Task::findOrFail($data['task_id']);
        $newDate = Carbon::parse($data['date'])->format('Y-m-d');
        $newTimeUsed = (float) ($data['time_used'] < 0 ? 1 : $data['time_used']);
        $newDescription = $data['description'] ?? null;

        $changes = [];

        if ($oldTaskId !== $newTask->id) {
            $newTaskTicket = $newTask->ticket_link ?? 'Unknown Task';
            $changes[] = "task from '{$oldTaskTicket}' to '{$newTaskTicket}'";
        }

        if ($oldDate !== $newDate) {
            $changes[] = "date from '{$oldDate}' to '{$newDate}'";
        }

        if ($oldTimeUsed !== $newTimeUsed) {
            $changes[] = "time_used from '{$oldTimeUsed}' to '{$newTimeUsed}'";
        }

        if ($oldDescription !== ($newDescription ?? 'None')) {
            $newDescLog = $newDescription ?? 'None';
            $changes[] = "description from '{$oldDescription}' to '{$newDescLog}'";
        }

        $logtime->update([
            'task_id'     => $newTask->id,
            'date'        => $newDate,
            'time_used'   => $newTimeUsed,
            'description' => $newDescription,
        ]);

        $logtime->load('task');

        if (!empty($changes)) {
            $detailLog = implode(', ', $changes);
            $this->createLog("[UPDATE] logtime {$oldTaskTicket} ({$detailLog})");
        }

        return $logtime;
    }

    public function deleteLogtime(int|string $id): Logtime
    {
        $user = Auth::user();

        $logtime = Logtime::with('task')->findOrFail($id);

        $taskTicket = $logtime->task->ticket_link ?? 'Unknown Task';
        $timeUsed = $logtime->time_used;
        $date = $logtime->date;

        $logtime->delete();

        $this->createLog("[DELETE] logtime {$taskTicket} for {$timeUsed} hours on {$date}");

        return $logtime;
    }

    public function exportLogtimes(array $query): array
    {
        $isSummary = isset($query['summary']) && filter_var($query['summary'], FILTER_VALIDATE_BOOLEAN);
        $filename = $isSummary ? 'summary_logtimes.xlsx' : 'logtimes.xlsx';

        if (isset($query['user_id'])) {
            $user = User::findOrFail($query['user_id']);
            if ($user) {
                $sanitizedName = preg_replace('/[^A-Za-z0-9\-]/', '_', $user->name);
                $filename = $isSummary 
                    ? "{$sanitizedName}_summary_logtime.xlsx" 
                    : "{$sanitizedName}_logtime.xlsx";
            }
        }

        $targetLog = $isSummary ? 'summary logtime' : 'logtime';
        $this->createLog("[EXPORT] {$targetLog}");

        return [
            'export'   => new LogtimeMultiSheetExport($query),
            'filename' => $filename,
        ];
    }

    public function createLog(string $description): void
    {
        Auth::user()->logs()->create([
            'target'        => 'logtime',
            'description'   => $description,
        ]);
    }
}