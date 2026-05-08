<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Base Reset */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #edf2f7; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #edf2f7; padding-bottom: 40px; }
        
        /* Container Kartu */
        .content { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        
        /* Header Biru Professional */
        .header { background-color: #2b6cb0; padding: 30px; text-align: center; }
        .header h2 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; letter-spacing: 0.5px; }
        
        /* Body Content */
        .body-section { padding: 30px; color: #4a5568; line-height: 1.6; }
        
        /* Highlight Role */
        .role-badge { background-color: #ebf8ff; color: #2b6cb0; padding: 4px 8px; border-radius: 4px; font-weight: bold; border: 1px solid #bee3f8; }

        /* Judul Task Besar */
        .task-title { font-size: 20px; font-weight: 700; color: #2d3748; margin: 20px 0 10px 0; line-height: 1.3; }
        
        /* Grid Informasi Task */
        .info-grid { display: table; width: 100%; margin-top: 20px; border-top: 1px solid #e2e8f0; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .label { font-size: 12px; text-transform: uppercase; color: #718096; font-weight: 600; width: 120px; }
        .value { color: #2d3748; font-weight: 500; }

        /* Link Ticket */
        .ticket-link { color: #3182ce; text-decoration: none; word-break: break-all; }
        .ticket-link:hover { text-decoration: underline; }

        /* Priority Colors */
        .priority-high { color: #e53e3e; font-weight: bold; }
        .priority-normal { color: #3182ce; font-weight: bold; }
        .priority-low { color: #38a169; font-weight: bold; }

        /* Tombol CTA */
        .btn-container { text-align: center; margin-top: 35px; }
        .btn { background-color: #2b6cb0; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background-color 0.3s; }
        .btn:hover { background-color: #2c5282; }
        
        /* Footer */
        .footer { background-color: #f7fafc; text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; border-top: 1px solid #edf2f7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <div class="content">
            <div class="header">
                <h2>📋 Penugasan Baru</h2>
            </div>

            <div class="body-section">
                <p>Halo,</p>
                <p>Kamu telah ditugaskan secara resmi sebagai <span class="role-badge">{{ $role }}</span> untuk mengerjakan task berikut:</p>
                
                <div class="task-title">
                    {{ $task->issue }}
                </div>

                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-cell label">Project</div>
                        <div class="info-cell value">{{ $task->project->name ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell label">Prioritas</div>
                        <div class="info-cell value">
                            @php $prio = strtolower($task->type); @endphp
                            <span class="@if($prio=='high') priority-high @elseif($prio=='normal') priority-normal @else priority-low @endif">
                                {{ ucfirst($task->type) }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell label">Ticket Link</div>
                        <div class="info-cell value">
                            <a href="//{{ $task->ticket_link }}" class="ticket-link" target="_blank">{{ $task->ticket_link }}</a>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-cell label">Deadline</div>
                        <div class="info-cell value">
                            {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <a href="{{ route('task.show', $task->id) }}" class="btn">Lihat Detail Task</a>
                </div>
            </div>

            <div class="footer">
                Dikirim otomatis oleh KNDI Task Manager System<br>
                &copy; {{ date('Y') }}
            </div>
        </div>
    </div>
</body>
</html>