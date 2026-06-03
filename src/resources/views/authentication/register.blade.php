<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - WearWoreWorn</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">

    <div class="bg-gray-800 p-8 rounded-xl shadow-lg w-full max-w-md border border-gray-700">
        <h1 class="text-3xl font-bold text-center mb-6 text-white">Buat Akun</h1>
        
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-400">Nama Lengkap</label>
                <input type="text" name="name" id="name" required 
                    class="mt-1 w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-400">Email</label>
                <input type="email" name="email" id="email" required 
                    class="mt-1 w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-400">Password</label>
                <input type="password" name="password" id="password" required 
                    class="mt-1 w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white">
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 mt-4">
                Daftar Sekarang
            </button>
            
        </form>

        <p class="mt-4 text-center text-sm text-gray-400">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-500 hover:underline">Masuk di sini</a>
        </p>
    </div>

</body>
</html>