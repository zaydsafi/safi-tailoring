<?php

namespace App\Support;

use Illuminate\Mail\MailManager;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

/**
 * SMTP mail manager with a TLS CA-bundle safety net.
 *
 * PHP servers whose install folder was moved (drive renamed on Windows,
 * ported build) keep openssl.cafile — and on some builds even the
 * compiled-in default cert file — pointing at a path that no longer
 * exists, and some builds refuse to re-point that ini at runtime. Every
 * SMTP-over-TLS connection then dies with "failed loading cafile stream".
 *
 * When that configuration is broken, this manager drops the CA bundle
 * shipped with the project (cacert.pem) into the SMTP stream context
 * directly. On hosts whose certificate configuration is fine it is a
 * no-op, so production keeps using the system trust store untouched.
 */
class TlsMailManager extends MailManager
{
    protected function configureSmtpTransport(EsmtpTransport $transport, array $config)
    {
        $this->applyCaBundleFallback($transport);

        return parent::configureSmtpTransport($transport, $config);
    }

    private function applyCaBundleFallback(EsmtpTransport $transport): void
    {
        $stream = $transport->getStream();

        if (! $stream instanceof SocketStream) {
            return;
        }

        $cafile = (string) (ini_get('openssl.cafile') ?: '');

        $runtimeCaBroken = PHP_OS_FAMILY === 'Windows'
            || ($cafile !== '' && ! is_file($cafile));

        if (! $runtimeCaBroken) {
            return;
        }

        $bundle = base_path('cacert.pem');

        if (! is_file($bundle)) {
            return;
        }

        $options = $stream->getStreamOptions();
        $options['ssl']['cafile'] = $bundle;
        $stream->setStreamOptions($options);
    }
}
