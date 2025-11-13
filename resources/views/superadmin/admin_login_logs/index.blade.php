<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login Location Logs - MCC Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --border: #e2e8f0;
            --accent: #2563eb;
            --accent-light: rgba(37, 99, 235, 0.08);
            --danger: #ef4444;
            --success: #16a34a;
            --radius: 16px;
            --shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 3rem 1.5rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header h1 span {
            background: var(--accent);
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .notice {
            background: var(--accent-light);
            border-left: 4px solid var(--accent);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .card-header {
            padding: 1.75rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 960px;
        }

        thead {
            background: rgba(15, 23, 42, 0.04);
        }

        th, td {
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        th {
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--muted);
        }

        tbody tr:hover {
            background: rgba(59, 130, 246, 0.05);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent);
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .location {
            font-size: 0.95rem;
            line-height: 1.5;
            display: grid;
            gap: 0.25rem;
        }

        .location small {
            color: var(--muted);
        }

        .empty {
            padding: 2rem;
            text-align: center;
            color: var(--muted);
        }

        .pagination {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            body {
                padding: 2rem 1rem;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .pagination {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <span><i class="fa-solid fa-location-dot"></i></span>
                Admin Login Location Logs
            </h1>
            <div class="notice">
                <strong>Reminder:</strong> IP-based geolocation is approximate (usually province or municipality only).
                When admins allow browser geolocation, the log updates to the more precise barangay-level location.
                Always inform admins and obtain consent before collecting device GPS data.
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fa-solid fa-list-check"></i> Recent Login Events</h2>
                <div class="pill"><i class="fa-solid fa-shield-halved"></i> Security Audit Trail</div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Role</th>
                            <th>Location</th>
                            <th>Coordinates</th>
                            <th>IP / ISP</th>
                            <th>Logged At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    <div>{{ optional($log->admin)->name ?? optional($log->admin)->username ?? 'Unknown Admin #'.$log->admin_id }}</div>
                                    <small class="pill" style="margin-top:0.35rem;"><i class="fa-solid fa-id-card"></i> ID: {{ $log->admin_id ?? 'n/a' }}</small>
                                </td>
                                <td>
                                    <span class="pill">
                                        <i class="fa-solid fa-user-shield"></i>
                                        {{ str_replace('_', ' ', $log->role ?? 'unknown') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="location">
                                        <strong>{{ $log->barangay ?? 'Barangay unknown' }}</strong>
                                        <span>{{ $log->city ?? 'Municipality unknown' }}</span>
                                        <span>{{ $log->province ?? 'Province unknown' }}</span>
                                        <small>{{ $log->country ?? 'Country unknown' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="location">
                                        <span>Lat: {{ $log->latitude ?? '—' }}</span>
                                        <span>Lng: {{ $log->longitude ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="location">
                                        <span>{{ $log->ip ?? 'N/A' }}</span>
                                        <small>{{ $log->isp ?? 'ISP unknown' }}</small>
                                        @if ($log->user_agent)
                                            <small title="{{ $log->user_agent }}">UA: {{ \Illuminate\Support\Str::limit($log->user_agent, 32) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="location">
                                        <span>{{ $log->logged_at?->format('M d, Y h:i A') ?? $log->created_at->format('M d, Y h:i A') }}</span>
                                        <small>{{ $log->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">
                                    <i class="fa-solid fa-circle-info"></i>
                                    No admin login events recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</body>
</html>

