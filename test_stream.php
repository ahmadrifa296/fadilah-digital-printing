<?php
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
        'cafile' => 'C:/xampp/php/extras/ssl/cacert.pem',
        'peer_name' => 'smtp.gmail.com',
    ]
]);

$client = stream_socket_client('tcp://smtp.gmail.com:587', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
if (!$client) {
    echo "Connection failed: $errstr ($errno)\n";
} else {
    echo "Connected successfully to TCP. Enabling crypto...\n";
    // Read greeting
    echo "Greeting: " . fgets($client);
    
    // Send EHLO
    fwrite($client, "EHLO localhost\r\n");
    echo "EHLO response:\n";
    while ($line = fgets($client)) {
        echo "  " . $line;
        if (preg_match('/^\d{3}\s/', $line)) break;
    }
    
    // Send STARTTLS
    fwrite($client, "STARTTLS\r\n");
    echo "STARTTLS response: " . fgets($client);
    
    // Set context options to ensure the stream context is applied during handshake
    stream_context_set_option($client, 'ssl', 'cafile', 'C:/xampp/php/extras/ssl/cacert.pem');
    stream_context_set_option($client, 'ssl', 'verify_peer', true);
    stream_context_set_option($client, 'ssl', 'verify_peer_name', true);
    stream_context_set_option($client, 'ssl', 'peer_name', 'smtp.gmail.com');
    
    // Enable crypto
    if (stream_socket_enable_crypto($client, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        echo "Crypto enabled successfully! SSL connection works!\n";
    } else {
        echo "Failed to enable crypto (SSL handshake failed).\n";
    }
    fclose($client);
}
