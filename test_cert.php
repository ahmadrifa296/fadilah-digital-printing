<?php
$context = stream_context_create([
    'ssl' => [
        'capture_peer_cert' => true,
        'capture_peer_cert_chain' => true,
        'verify_peer' => false, 
        'verify_peer_name' => false,
    ]
]);

$client = stream_socket_client('tcp://smtp.gmail.com:587', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
if ($client) {
    fgets($client); // Greeting
    fwrite($client, "EHLO localhost\r\n");
    while ($line = fgets($client)) {
        if (preg_match('/^\d{3}\s/', $line)) break;
    }
    fwrite($client, "STARTTLS\r\n");
    fgets($client);
    
    stream_socket_enable_crypto($client, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    $params = stream_context_get_params($client);
    if (isset($params['options']['ssl']['peer_certificate'])) {
        $cert = $params['options']['ssl']['peer_certificate'];
        $cert_info = openssl_x509_parse($cert);
        
        echo "CN: " . ($cert_info['subject']['CN'] ?? 'N/A') . "\n";
        echo "Issuer CN: " . ($cert_info['issuer']['CN'] ?? 'N/A') . "\n";
        echo "Issuer Org: " . ($cert_info['issuer']['O'] ?? 'N/A') . "\n";
    } else {
        echo "No certificate captured.\n";
    }
    fclose($client);
} else {
    echo "Connection failed: $errstr ($errno)\n";
}
