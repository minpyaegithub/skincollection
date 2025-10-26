<!DOCTYPE html>
<html>
<head>
    <title>Test Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @livewireStyles
</head>
<body>
    <div class="container mt-5">
        <h1>Test Appointments Calendar</h1>
        
        <div class="alert alert-info">
            <strong>Debug:</strong> Testing Livewire component
        </div>
        
        @livewire('appointments-calendar')
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
