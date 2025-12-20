<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TU-North CPMS</title>
    <meta name="csrf-token" content="<?php echo (new Controller())->generateCsrfToken(); ?>">
    <!-- Google Font: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <?php
        $faviconUrl = '';
        if (!empty($data['site_favicon'])) {
            $faviconUrl = BASE_URL . '/' . $data['site_favicon'];
        } else if (isset($this) && method_exists($this, 'model')) {
            try {
                $settingsModel = $this->model('Settings_model');
                $favPath = $settingsModel->get('site_favicon');
                if ($favPath) {
                    $faviconUrl = BASE_URL . '/' . $favPath;
                }
            } catch (Exception $e) {}
        }
        
        // Default Fallback
        if (empty($faviconUrl)) {
            $faviconUrl = 'https://www.tn.ac.th/wp-content/uploads/2021/10/cropped-logo-tn-32x32.png';
        }
    ?>
    <link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($faviconUrl); ?>">
    
    <!-- Tailwind CSS v4 (CDN) -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
    <script>
        // ส่งค่า BASE_URL จาก PHP ไปให้ JavaScript ใช้
        window.BASE_URL = "<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>";
    </script>
</head>
<body class="bg-gray-50">