<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MCC Portal')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .navbar-nav a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .navbar-nav a:hover {
            color: #3b82f6;
        }

        .main-content {
            margin-top: 80px;
        }

        .footer {
            background: #1f2937;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .navbar-nav {
                gap: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="{{ route('welcome') }}" class="navbar-brand">
                <i class="fas fa-graduation-cap"></i> MCC Portal
            </a>
            <ul class="navbar-nav">
                <!-- Navigation links removed for cleaner interface -->
            </ul>
        </div>
    </nav>

    <main class="main-content">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Madridejos Community College. All rights reserved.</p>
            <p>Stay connected with the latest updates from MCC</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
