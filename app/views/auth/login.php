<?php require_once '../app/views/templates/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl">
        <div>
            <img class="mx-auto h-24 w-auto" src="http://www.tn.ac.th/_files_school/65012004/data/65012004_0_20241212-145309.png" alt="Logo">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                CPMS Login
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                ระบบจัดการโครงงานคอมพิวเตอร์ ม.6 <br>
                <span class="font-medium text-pink-600">โรงเรียนเตรียมอุดมศึกษา ภาคเหนือ</span>
            </p>
        </div>
        
        <form id="loginForm" class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">อีเมล (Email)</label>
                    <input name="email" type="email" required class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm" placeholder="3xxxx@tn.ac.th">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">รหัสผ่าน (Password)</label>
                    <input name="password" type="password" required class="appearance-none rounded-lg relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-pink-500 focus:border-pink-500 focus:z-10 sm:text-sm" placeholder="••••••••">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                    เข้าสู่ระบบ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    try {
        const response = await fetch('/auth/login', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: result.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '/dashboard';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ผิดพลาด',
                text: result.message
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
});
</script>

<?php require_once '../app/views/templates/footer.php'; ?>