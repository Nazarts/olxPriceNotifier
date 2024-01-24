<?php

namespace Services;

class EmailService
{
    private static string $queueFileName = 'undelivered.json';

    private static function saveUndeliveredMessages($recipient, $subject, $message): void
    {
        $static_path = array_key_exists('STATIC_PATH', $_ENV)? $_ENV['STATIC_PATH']: '';
        $full_path = $static_path.'/'.self::$queueFileName;
        $f_content = file_get_contents($full_path);
        if ($f_content === false) {
            $f_content = '';
        }
        $undelivered_messages = json_decode($f_content, true);
        $undelivered_messages[] = [
            'recipient' => $recipient,
            'subject' => $subject,
            'message' => $message
        ];
        $f_content = json_encode($undelivered_messages);
        file_put_contents($full_path, $f_content);
    }
    public static function sendEmail($recipient, $subject, $message): bool
    {
        $headers = "From: nazik.sprey2000@gmail.com";
        // Compose the email headers and message
        $fullMessage = wordwrap($message, 70);

        // Send the email
        $hasSent = mail($recipient, $subject, $fullMessage, $headers);
        if (!$hasSent) {
            try {
                self::saveUndeliveredMessages($recipient, $subject, $message);
            } catch (\Exception $exception) {

            }
        }
        return $hasSent;
    }
}