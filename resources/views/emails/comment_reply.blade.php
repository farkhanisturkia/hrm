<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #edf2f7; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #edf2f7; padding-bottom: 40px; }
        .content { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        
        /* Header Hijau untuk Reply */
        .header { background-color: #38a169; padding: 25px; text-align: center; }
        .header h2 { color: #ffffff; margin: 0; font-size: 20px; letter-spacing: 0.5px; }
        
        .body-section { padding: 30px; color: #4a5568; line-height: 1.6; }
        .task-title { color: #2d3748; font-size: 18px; font-weight: bold; margin: 10px 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        
        /* Kotak Reply Hijau Muda */
        .reply-box { background-color: #f0fff4; border-left: 5px solid #48bb78; padding: 15px 20px; margin: 20px 0; border-radius: 4px; color: #22543d; }
        
        .meta-info { font-size: 12px; color: #718096; margin-bottom: 5px; display: block; font-weight: 600; }
        
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { background-color: #38a169; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block; }
        .btn:hover { background-color: #2f855a; }
        
        .footer { background-color: #edf2f7; text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <div class="content">
            <div class="header">
                <h2>↩️ Balasan Baru</h2>
            </div>

            <div class="body-section">
                <p>Halo,</p>
                <p>Komentar kamu di task <strong>{{ $task->issue }}</strong> telah dibalas oleh <strong>{{ $replier->name }}</strong>.</p>

                <br>
                <span class="meta-info">
                    📅 {{ $replyData->created_at->translatedFormat('l, d F Y') }} &bull; ⏰ {{ $replyData->created_at->format('H:i') }} WIB
                </span>

                <div class="reply-box">
                    <strong>{{ $replier->name }} berkata:</strong><br><br>
                    "{{ $replyData->comment }}"
                </div>

                <div class="btn-container">
                    <a href="{{ route('task.show', $task->id) }}" class="btn">Balas Kembali</a>
                </div>
            </div>
        </div>
        
        <div class="footer">
            Dikirim otomatis oleh KNDI Task Manager System<br>
            &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>