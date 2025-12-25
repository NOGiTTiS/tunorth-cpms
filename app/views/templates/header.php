<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $systemName = 'TU-North CPMS'; // Default
        if (!empty($data['settings']['system_name'])) {
            $systemName = $data['settings']['system_name'];
        } else if (isset($this) && method_exists($this, 'model')) {
            try {
                $settingsModel = $this->model('Settings_model');
                $dbName = $settingsModel->get('system_name');
                if ($dbName) {
                    $systemName = $dbName;
                }
            } catch (Exception $e) {}
        }
    ?>
    <title><?php echo htmlspecialchars($systemName); ?></title>
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

        document.addEventListener("DOMContentLoaded", function() {
            let activeFetches = 0;
            const loader = document.getElementById('global-loader');

            const showLoader = () => {
                activeFetches++;
                if (activeFetches > 0 && loader) loader.classList.remove('hidden');
            };

            const hideLoader = () => {
                activeFetches--;
                if (activeFetches <= 0) {
                    activeFetches = 0;
                    if (loader) loader.classList.add('hidden');
                }
            };

            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                showLoader();
                try {
                    const response = await originalFetch(...args);
                    return response;
                } catch (error) {
                    throw error;
                } finally {
                    hideLoader();
                }
            };
        });
    </script>
</head>
<body class="bg-gray-50">
    <!-- Global Unified Loader -->
    <div id="global-loader" class="fixed inset-0 z-[9999] bg-white/50 backdrop-blur-[2px] flex items-center justify-center hidden transition-all duration-300">
        <div class="relative flex flex-col items-center">
            <div class="w-16 h-16 border-4 border-pink-100 border-t-pink-600 rounded-full animate-spin shadow-lg"></div>
            <p class="mt-4 text-pink-600 font-bold text-sm animate-pulse tracking-widest">LOADING...</p>
        </div>
    </div>