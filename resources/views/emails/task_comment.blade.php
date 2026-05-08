<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #edf2f7; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #edf2f7; padding-bottom: 40px; }
        .content { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        
        /* Header Biru */
        .header { background-color: #2d3748; padding: 25px; text-align: center; }
        .header h2 { color: #ffffff; margin: 0; font-size: 20px; letter-spacing: 0.5px; }
        
        /* Body */
        .body-section { padding: 30px; color: #4a5568; line-height: 1.6; }
        .task-title { color: #2d3748; font-size: 18px; font-weight: bold; margin: 10px 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        
        /* Kotak Komentar */
        .comment-box { background-color: #f7fafc; border-left: 5px solid #4299e1; padding: 15px 20px; margin: 20px 0; border-radius: 4px; font-style: italic; color: #2c5282; }
        
        /* Info Tanggal (Kecil di atas komentar) */
        .meta-info { font-size: 12px; color: #718096; margin-bottom: 5px; display: block; font-weight: 600; }
        
        /* Tombol */
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { background-color: #4299e1; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; display: inline-block; transition: background-color 0.3s; }
        .btn:hover { background-color: #3182ce; }
        
        /* Footer */
        .footer { background-color: #edf2f7; text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <div class="content">
            <div class="header">
                <h2>💬 Komentar Baru</h2>
            </div>

            <div class="body-section">
                <p>Halo,</p>
                <p><strong>{{ $sender->name }}</strong> menambahkan komentar baru pada task:</p>
                
                <div class="task-title">
                    #{{ $task->id }} - {{ $task->issue }}
                </div>

                <br>
                <span class="meta-info">
                    📅 {{ $commentData->created_at->translatedFormat('l, d F Y') }} &bull; ⏰ {{ $commentData->created_at->format('H:i') }} WIB
                </span>

                <div class="comment-box">
                    "{{ $commentData->comment }}"
                </div>

                <div class="btn-container">
                    <a href="{{ route('task.show', $task->id) }}" class="btn">Lihat & Balas</a>
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