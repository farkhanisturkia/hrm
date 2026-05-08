<p>Task: {{ $task->issue }}</p>
<p>Reviewer: {{ $reviewer->name }} &lt;{{ $reviewer->email }}&gt;</p>
@if(!empty($reply->pr_links))
<ul>
@foreach($reply->pr_links as $link)
    <li><a href="//{{ $link }}">{{ $link }}</a></li>
@endforeach
</ul>
@endif
<p>Comment:</p>
<p>{{ $reply->comment }}</p>
<p>Link to task: <a href="{{ url('/task/'.$task->id) }}">{{ url('/task/'.$task->id) }}</a></p>
