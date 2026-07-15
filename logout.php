<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url=index.php">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging out...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('pictures/iccbackground.png') no-repeat center center fixed;
            background-size: cover;
            background-blend-mode: lighten;
            background-color: rgba(255, 255, 255, 0.85);
        }

        /* Fade in and scale container */
        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.85); }
            100% { opacity: 1; transform: scale(1); }
        }

        /* Bounce icon animation */
        @keyframes bouncePulse {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15%); }
        }

        /* Spinner rotation synchronized with 2s */
        @keyframes spinEaseOut {
            0% { transform: rotate(0deg); }
            90% { transform: rotate(342deg); }
            100% { transform: rotate(360deg); }
        }

        /* Progress bar fill animation */
        @keyframes fillProgress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        .animate-fadeInScale { animation: fadeInScale 0.8s ease forwards; }
        .animate-bouncePulse { animation: bouncePulse 1s ease-in-out infinite; }
        .animate-spinEaseOut { animation: spinEaseOut 2s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .animate-fillProgress { animation: fillProgress 2s linear forwards; }

        .spinner-ring {
            border-top: 4px solid #3b82f6; 
            border-right: 4px solid transparent;
            border-bottom: 4px solid #3b82f6;
            border-left: 4px solid transparent;
            border-radius: 50%;
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1.5rem;
        }

        /* Frosted overlay */
        .overlay {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center font-sans">

    <div class="overlay p-8 md:p-10 max-w-md w-full rounded-3xl shadow-2xl text-center animate-fadeInScale">
        <!-- Animated logout icon -->
        <i class="fas fa-sign-out-alt text-6xl text-blue-600 mb-4 animate-bouncePulse"></i>
        
        <!-- Heading -->
        <h1 class="text-3xl md:text-4xl font-extrabold text-blue-600 mb-2">Logging Out...</h1>

        <!-- Description -->
        <p class="text-gray-700 text-base md:text-lg mb-6">
            You are being securely logged out.<br>
            You will be redirected to the homepage shortly.<br>
            If you are not redirected, 
            <a href="index.php" class="text-blue-600 font-semibold hover:text-blue-800 hover:underline transition">click here</a>.
        </p>

        <!-- Spinner synchronized with progress bar -->
        <div class="spinner-ring animate-spinEaseOut"></div>

        <!-- Progress bar -->
        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
            <div class="h-2 bg-blue-600 rounded-full animate-fillProgress"></div>
        </div>
    </div>

</body>
</html>
