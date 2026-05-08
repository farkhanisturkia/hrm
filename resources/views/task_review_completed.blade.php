<p>Review marked as completed for task: {{ $task->issue }}</p>
<p>Reviewer: {{ $reviewer->user->name ?? 'N/A' }}</p>
<p>Completed at: {{ $reviewer->completed_at }}</p>
<p>Link to task: <a href="{{ url('/task/'.$task->id) }}">{{ url('/task/'.$task->id) }}</a></p>
