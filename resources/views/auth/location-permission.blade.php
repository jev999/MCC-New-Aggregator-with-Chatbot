<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Allow Location</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, Arial; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; background:#f5f5f5; }
        .card { border:1px solid #ddd; padding:28px; border-radius:8px; width:420px; text-align:center; background:white; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top:0; color:#333; }
        p { color:#666; line-height:1.6; }
        button { padding:10px 16px; border-radius:6px; cursor:pointer; background:#007bff; color:white; border:none; font-size:16px; }
        button:hover { background:#0056b3; }
        .info { margin-top:12px; color:#666; font-size:14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Allow location access</h2>
        <p>To continue to login, please allow location access. Only latitude/longitude will be recorded.</p>

        <button id="allowBtn">Allow Location</button>
        <p class="info">
            If you deny, you cannot proceed to login.
        </p>

        <form id="locForm" method="POST" action="{{ route('save.location') }}" style="display:none;">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="allow" value="1">
        </form>
    </div>

<script>
document.getElementById('allowBtn').addEventListener('click', function(){
    if (!navigator.geolocation) {
        alert('Geolocation not supported by your browser.');
        return;
    }

    // Request high accuracy if available
    navigator.geolocation.getCurrentPosition(function(position) {
        document.getElementById('latitude').value = position.coords.latitude;
        document.getElementById('longitude').value = position.coords.longitude;
        document.getElementById('locForm').submit();
    }, function(err) {
        // handle errors
        if (err.code === err.PERMISSION_DENIED) {
            alert('Location permission denied. Please enable location to proceed.');
        } else {
            alert('Unable to retrieve your location. Try again.');
        }
    }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });
});
</script>
</body>
</html>

