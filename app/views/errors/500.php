<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error - TU-North CPMS</title>
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 text-center">
    <div class="max-w-md w-full bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
        <div class="w-24 h-24 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl">
            💥
        </div>
        <h1 class="text-6xl font-black text-slate-800 mb-2">500</h1>
        <h2 class="text-xl font-bold text-gray-700 mb-4">ระบบเกิดข้อผิดพลาดภายใน</h2>
        <p class="text-gray-400 mb-8 text-sm leading-relaxed">
            เซิร์ฟเวอร์พบปัญหาบางอย่างและไม่สามารถดำเนินการตามคำขอของคุณได้ กรุณาลองใหม่อีกครั้งในภายหลัง
        </p>
        
        <?php if(isset($data['error']) || isset($e)): ?>
            <div class="bg-red-50 p-4 rounded-xl text-left mb-6 overflow-auto max-h-40">
                <p class="text-red-800 font-mono text-xs">
                    <?php 
                        echo htmlspecialchars($data['error'] ?? '');
                        if(isset($e)) echo "<br>" . htmlspecialchars($e->getMessage()); 
                    ?>
                </p>
            </div>
        <?php endif; ?>
        
        <div class="space-y-3">
            <button onclick="location.reload()" class="w-full px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold text-sm hover:bg-slate-800 transition-colors shadow-lg">
                ลองใหม่อีกครั้ง (Refresh)
            </button>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="block w-full px-8 py-3 bg-gray-100 text-gray-500 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-colors">
                กลับไปหน้าหลัก
            </a>
        </div>
    </div>
</body>
</html>
