<?php

set_time_limit(0);

//https://www.dunnies-it.com/php/a-php-imap-script-to-download-gmail-attachments.php

//Créer son mot de passe https://devanswe.rs/enable-2-step-verification-google-account/

$mailbox = $_ENV['ATTACHMENTS_MAILBOX'];
$username = $_ENV['ATTACHMENTS_USER'];
$password = $_ENV['ATTACHMENTS_PASS'];
$criteria = $_ENV['ATTACHMENTS_QUERY'];

echo 'Starting...'.PHP_EOL;

$inbox = imap_open($mailbox, $username, $password, OP_READONLY) or die('Cannot connect to Gmail: '.imap_last_error());
$emails = imap_search($inbox, $criteria); // finds all incoming mail from "person" containing partial text in subject 'something in subject'

if (false === $emails) {
    echo 'Error in imap query';
    exit(1);
}

if (is_array($emails) && 0 === count($emails)) {
    imap_close($inbox);

    echo 'No mail found';
    exit;
}

rsort($emails);
foreach ($emails as $email_number) {
    $attachments = null;
    $overview = imap_fetch_overview($inbox, $email_number, 0);
    $message = imap_fetchbody($inbox, $email_number, 2);
    $structure = imap_fetchstructure($inbox, $email_number);

    $subject = mb_decode_mimeheader($overview[0]->subject);
    $date = (new DateTime($overview[0]->date))->format('Y-m-d');

    if (!is_iterable($structure->parts)) {
        continue;
    }

    foreach ($structure->parts as $i => $part) {
        $attachments[$i] = [
            'is_attachment' => false,
            'filename' => '',
            'name' => '',
            'attachment' => ''
        ];

        if ($part->dparameters) {
            foreach ($part->dparameters as $object) {
                if (strtolower($object->attribute) == 'filename') {
                    $attachments[$i]['is_attachment'] = true;
                    $attachments[$i]['filename'] = $object->value;
                }
            }
        }

        if ($part->ifparameters) {
            foreach ($part->parameters as $object) {
                if (strtolower($object->attribute) == 'name') {
                    $attachments[$i]['is_attachment'] = true;
                    $attachments[$i]['name'] = $object->value;
                }
            }
        }

        if ($attachments[$i]['is_attachment']) {
            $attachment = imap_fetchbody($inbox, $email_number, $i + 1);
            if ($part->encoding == 3) {
                $attachments[$i]['attachment'] = base64_decode($attachment);
            } elseif ($part->encoding == 4) {
                $attachments[$i]['attachment'] = quoted_printable_decode($attachment);
            }
        }
    }

    if (!$attachments || !is_iterable($attachments)) {
        continue;
    }

    foreach ($attachments as $i => $attachment) {
        if (true === $attachment['is_attachment']) {
            $filename = $attachment['name'] ?? $attachment['filename'];
            $extension = strtolower(pathinfo($filename)['extension']);

            if (in_array($extension, ['jpg','png','gif',])) {
                echo($filename.' skiped'.PHP_EOL);
                continue;
            }

            $filename = sprintf('%s - %s (%s).%s',
                $date,
                $subject,
                $i,
                $extension
            );

            echo($filename);

            if (file_exists($filename)) {
                echo('.. file already exists!'.PHP_EOL);
            } else {
                echo('.. saving'.PHP_EOL);
                $fp = fopen('dwn/'.$filename, "w+");
                fwrite($fp, $attachment['attachment']);
                fclose($fp);
            }
        }
    }
}

imap_close($inbox);

echo 'Done'.PHP_EOL;

