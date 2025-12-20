<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - TU-North CPMS</title>
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
        <div class="w-24 h-24 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 text-5xl animate-bounce">
            🔦
        </div>
        <h1 class="text-6xl font-black text-slate-800 mb-2">404</h1>
        <h2 class="text-xl font-bold text-gray-700 mb-4">ไม่พบหน้าที่คุณต้องการ</h2>
        <p class="text-gray-400 mb-8 text-sm leading-relaxed">
            ดูเหมือนว่าหน้าที่คุณกำลังค้นหาอาจถูกลบไปแล้ว เปลี่ยนชื่อ หรือไม่มีอยู่จริงกรุณาตรวจสอบ URL อีกครั้ง
        </p>
        
        <a href="<?php echo BASE_URL; ?>/dashboard" class="inline-block px-8 py-3 bg-slate-900 text-white rounded-2xl font-bold text-sm hover:bg-pink-600 transition-colors shadow-lg shadow-slate-200">
            กลับไปหน้าหลัก
        </a>
    </div>
</body>
</html>
