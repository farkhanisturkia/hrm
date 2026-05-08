<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\PullRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request): Response
    {
        $tasks    = $this->taskService->getFilteredTasks($request->query());
        $projects = Project::with('projectOwner')->where('isDeleted', false)->get();

        return Inertia::render('Task/Index', [
            ...$this->taskService->getFormData(),
            'tasks'    => $tasks,
            'projects' => $projects,
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $task = $this->taskService->store($request->validated());

        return back()->with('success', "Task '{$task->issue}' berhasil dibuat!");
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->taskService->update($task, $request->validated());

        return back()->with('success', "Task '{$task->issue}' berhasil diperbarui!");
    }

    public function show(Task $task): Response
    {
        $task->load([
            'logtimes',
            'reviewers.user',
            'pullRequests.replies.user',
            'pullRequests.user',
            'project.projectOwner',
        ]);

        $projects = Project::with('projectOwner')->get();

        return Inertia::render('Task/Show', [
            ...$this->taskService->getFormData(),
            'task'          => $task,
            'project'       => $task->project,
            'projects'      => $projects,
            'totalTimeUsed' => $task->logtimes->sum('time_used'),
            'prs'           => $task->pullRequests,
        ]);
    }

    public function assignTask(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'pl'           => 'nullable|numeric',
            'communicator' => 'array',
            'programmer'   => 'array',
            'designer'     => 'array',
        ]);

        $this->taskService->assignTask($task, $request->only(['pl', 'communicator', 'programmer', 'designer']));

        return back()->with('success', 'Task berhasil di-assign!');
    }

    public function prTask(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'reviewer' => 'array',
        ]);

        $this->taskService->assignReviewer($task, $request->reviewer ?? []);

        return back()->with('success', 'Reviewer berhasil diupdate!');
    }

    public function commentTask(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'pr_links' => 'nullable|array',
            'comment'  => 'required|string',
        ]);

        $this->taskService->addComment($task, $request->only(['pr_links', 'comment']));

        return back()->with('success', 'Komentar berhasil ditambahkan!');
    }

    public function replyTask(Request $request, PullRequest $pullRequest): RedirectResponse
    {
        $request->validate([
            'pr_links' => 'nullable|array',
            'comment'  => 'required|string',
        ]);

        $reply = $this->taskService->addReply($pullRequest, $request->only(['pr_links', 'comment']));

        $message = $reply
            ? 'Reply berhasil dikirim!'
            : 'Reply berhasil (terdeteksi duplikat, diabaikan)';

        return back()->with('success', $message);
    }

    public function changeIsActive(Task $task): RedirectResponse
    {
        $this->taskService->activeTask($task);

        return back()->with('success', 'Status active berhasil diperbarui!');
    }

    public function close(Task $task): RedirectResponse
    {
        $this->taskService->closeTask($task);

        return redirect()->route('task.list')->with('warning', "Task '{$task->issue}' berhasil diclose!");
    }

    public function destroy(Task $task): RedirectResponse
    {
        $issue = $task->issue;
        $this->taskService->destroy($task);

        return redirect()->route('task.list')->with('warning', "Task '{$issue}' berhasil dihapus!");
    }

    public function markReviewComplete(int $taskId, int $reviewerId)
    {
        abort_if(auth()->id() !== $reviewerId, 403);

        $this->taskService->markReviewComplete($taskId, $reviewerId);

        return response()->json(['message' => 'Marked as reviewed']);
    }
}