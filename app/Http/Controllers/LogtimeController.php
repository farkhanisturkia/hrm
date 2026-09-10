<?php

namespace App\Http\Controllers;

use App\Services\LogtimeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogtimeController extends Controller
{
    public function __construct(
        protected LogtimeService $logtimeService
    ) {}

    public function index(Request $request): Response
    {
        $query = $request->query();

        return Inertia::render('User/Logtime', [
            'logtimes' => $this->logtimeService->getLogtimes($query),
            'tasks'    => $this->logtimeService->getTasksForAuthUser(),
            'users'    => $this->logtimeService->getUsers(),
        ]);   
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'        => 'required|date',
            'task_id'     => 'required|exists:tasks,id',
            'time_used'   => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $logtime = $this->logtimeService->storeLogtime($validated);
        $taskTicket = $logtime->task->ticket_link ?? 'Task';

        return back()->with('success', "The log entry {$taskTicket} for {$logtime->time_used} hours on {$logtime->date} was successfully added!");
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $logtime = $this->logtimeService->deleteLogtime($id);
        $taskTicket = $logtime->task->ticket_link ?? 'Unknown Task';

        return back()->with('success', "The {$taskTicket} log entry, which lasted {$logtime->time_used} hours on {$logtime->date}, has been successfully deleted!");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $exportData = $this->logtimeService->exportLogtimes($request->query());

        return Excel::download($exportData['export'], $exportData['filename']);
    }
}