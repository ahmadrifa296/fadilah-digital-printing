<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Secara dinamis mengarahkan OpenSSL CAfile ke bundle sertifikat yang dikonfigurasi
        $certPath = env('SSL_CERT_FILE', 'C:/xampp/php/extras/ssl/cacert.pem');
        if (file_exists($certPath)) {
            putenv('SSL_CERT_FILE=' . $certPath);
        } elseif (file_exists(base_path('cacert.pem'))) {
            putenv('SSL_CERT_FILE=' . base_path('cacert.pem'));
        }

        // Daftarkan kustomisasi SMTP Transport agar mematuhi opsi stream SSL (verify_peer & cafile)
        \Illuminate\Support\Facades\Mail::extend('smtp', function (array $config) {
            $factory = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

            $scheme = $config['scheme'] ?? null;
            if (! $scheme) {
                $scheme = ($config['port'] == 465) ? 'smtps' : 'smtp';
            }

            $transport = $factory->create(new \Symfony\Component\Mailer\Transport\Dsn(
                $scheme,
                $config['host'],
                $config['username'] ?? null,
                $config['password'] ?? null,
                $config['port'] ?? null,
                $config
            ));

            $stream = $transport->getStream();
            if ($stream instanceof \Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream) {
                if (isset($config['source_ip'])) {
                    $stream->setSourceIp($config['source_ip']);
                }
                if (isset($config['timeout'])) {
                    $stream->setTimeout($config['timeout']);
                }
                // Masukkan opsi context stream kustom (SSL verify_peer & cafile)
                if (isset($config['stream'])) {
                    $stream->setStreamOptions($config['stream']);
                }
            }

            return $transport;
        });
    }
}
