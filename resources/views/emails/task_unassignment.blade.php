<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f6f8; padding-bottom: 40px; }
        .content { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; }
        
        /* Header Merah untuk Unassign */
        .header { background-color: #e53e3e; padding: 25px; text-align: center; }
        .header h2 { color: #ffffff; margin: 0; font-size: 20px; letter-spacing: 0.5px; }
        
        .body-section { padding: 30px; color: #4a5568; line-height: 1.6; }
        .task-title { color: #2d3748; font-size: 18px; font-weight: bold; margin: 10px 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        
        .info-box { background-color: #fff5f5; border-left: 5px solid #e53e3e; padding: 15px 20px; margin: 20px 0; border-radius: 4px; color: #742a2a; }
        
        .meta-info { font-size: 12px; color: #718096; margin-top: 10px; display: block; }
        
        .footer { background-color: #f7fafc; text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <br>
        <div class="content">
            <div class="header">
                <h2>⚠️ Perubahan Tugas</h2>
            </div>

            <div class="body-section">
                <p>Halo,</p>
                <p>Ini adalah notifikasi otomatis untuk memberitahu bahwa kamu <strong>tidak lagi ditugaskan</strong> sebagai <strong>{{ $role }}</strong> pada:</p>
                
                <div class="task-title">
                    #{{ $task->id }} - {{ $task->issue }}
                </div>

                <div class="info-box">
                    Kamu telah dihapus dari daftar {{ $role }} untuk tugas ini. Akses kamu ke tugas tersebut mungkin telah dicabut.
                </div>

                <p>Jika kamu merasa ini adalah kesalahan, silakan hubungi Project Manager.</p>

                <span class="meta-info">
                    Diupdate pada: {{ date('d M Y, H:i') }} WIB
                </span>
            </div>
        </div>
        
        <div class="footer">
            Dikirim otomatis oleh KNDI Task Manager System
        </div>
    </div>
</body>
</html>