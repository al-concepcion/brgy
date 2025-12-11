<?php
// View PHP error logs
$log_file = 'D:\xampp\php\logs\php_error_log';

if (!file_exists($log_file)) {
    $log_file = ini_get('error_log');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error Log Viewer</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .log-entry { padding: 10px; margin: 5px 0; background: #2d2d2d; border-left: 3px solid #007acc; }
        .error { border-left-color: #f44336; }
        .success { border-left-color: #4caf50; }
        .warning { border-left-color: #ff9800; }
        h1 { color: #fff; }
        .refresh { background: #007acc; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        .clear { background: #f44336; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; margin-left: 10px; }
    </style>
</head>
<body>
    <h1>📋 PHP Error Log</h1>
    <p><strong>Log file:</strong> <?php echo htmlspecialchars($log_file); ?></p>
    <button class="refresh" onclick="location.reload()">🔄 Refresh</button>
    <button class="clear" onclick="if(confirm('Clear log file?')) location.href='?clear=1'">🗑️ Clear Log</button>
    <hr>
    
    <?php
    if (isset($_GET['clear'])) {
        file_put_contents($log_file, '');
        echo "<p style='color: #4caf50;'>✓ Log cleared!</p>";
        echo "<script>setTimeout(() => location.href='view-logs.php', 1000);</script>";
    }
    
    if (file_exists($log_file)) {
        $logs = file($log_file);
        $logs = array_reverse($logs); // Show newest first
        $count = 0;
        
        foreach($logs as $line) {
            if (empty(trim($line))) continue;
            
            $class = 'log-entry';
            if (stripos($line, 'error') !== false || stripos($line, 'failed') !== false) {
                $class .= ' error';
            } elseif (stripos($line, 'success') !== false) {
                $class .= ' success';
            } elseif (stripos($line, 'warning') !== false) {
                $class .= ' warning';
            }
            
            echo "<div class='$class'>" . htmlspecialchars($line) . "</div>";
            
            $count++;
            if ($count >= 100) break; // Show last 100 entries
        }
        
        if ($count == 0) {
            echo "<p style='color: #888;'>No log entries found.</p>";
        }
    } else {
        echo "<p style='color: #f44336;'>Log file not found: " . htmlspecialchars($log_file) . "</p>";
    }
    ?>
    
    <script>
        // Auto-refresh every 5 seconds
        setTimeout(() => location.reload(), 5000);
    </script>
</body>
</html>
