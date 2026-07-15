<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="max-w-md w-full bg-white shadow-lg rounded-xl overflow-hidden animate-fadeIn">
        
        <!-- Form Header -->
        <div class="bg-blue-600 text-white text-center py-6 font-bold text-xl flex items-center justify-center gap-2">
            <i class="bi bi-box-arrow-in-right"></i> Log In
        </div>

        <!-- Form Body -->
        <div class="p-6 space-y-4">
            <?php if (!empty($error)): ?>
                <div class="p-3 rounded bg-red-100 text-red-800 font-medium"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off" class="space-y-4">
                
                <!-- Username -->
                <div>
                    <label for="username" class="block text-gray-700 font-medium mb-1">
                        <i class="bi bi-person"></i> Username
                    </label>
                    <input type="text" name="username" required autofocus
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-1">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400" />
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white font-semibold py-2 rounded-full shadow hover:bg-blue-500 transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i> Log In
                </button>              
            </form>
              <p class="text-gray-600 text-center mt-4">
    Don't have an account?
    <a href="register.php" class="text-blue-600 font-semibold hover:underline">Register here</a>
</p>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
    .animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
</style>
