<?php

set_time_limit(0);

//https://www.dunnies-it.com/php/a-php-imap-script-to-download-gmail-attachments.php

//Créer son mot de passe https://devanswe.rs/enable-2-step-verification-google-account/

$mailbox = $_ENV['ATTACHMENTS_MAILBOX'];
$username = $_ENV['ATTACHMENTS_USER'];
$password = $_ENV['ATTACHMENTS_PASS'];
$criteria = $_ENV['ATTACHMENTS_QUERY'];

$inbox = imap_open($mailbox, $username, $password) or die('Cannot connect to Gmail: '.imap_last_error());

$emails = imap_search($inbox, $criteria); // finds all incoming mail from "person" containing partial text in subject 'something in subject'

if (is_array($emails) && 0 === count($emails)) {
    imap_close($inbox);

    echo "No mail found";
    exit;
}

if (false === $emails) {
    imap_close($inbox);

    echo "Error in imap query";
    exit;
}


$count = 1;
rsort($emails);
foreach ($emails as $email_number) {
    $overview = imap_fetch_overview($inbox, $email_number, 0);
    $message = imap_fetchbody($inbox, $email_number, 2);
    $structure = imap_fetchstructure($inbox, $email_number);

        foreach ($structure->parts as $i => $part) {
            $attachments[$i] = array(
                'is_attachment' => false,
                'filename' => '',
                'name' => '',
                'attachment' => ''
            );

            if ($structure->parts[$i]->dparameters) {
                foreach ($structure->parts[$i]->dparameters as $object) {
                    if (strtolower($object->attribute) == 'filename') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['filename'] = $object->value;
                    }
                }
            }

            if ($structure->parts[$i]->ifparameters) {
                foreach ($structure->parts[$i]->parameters as $object) {
                    if (strtolower($object->attribute) == 'name') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['name'] = $object->value;
                    }
                }
            }

            if ($attachments[$i]['is_attachment']) {
                $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);

                if ($structure->parts[$i]->encoding == 3) {
                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                } elseif ($structure->parts[$i]->encoding == 4) {
                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                }
            }
        }
    }

    //Filter sur les PDF seulement
    //Renommer les fichiers date - sujet
    foreach ($attachments as $attachment) {
        if ($attachment['is_attachment'] == 1) {
            $filename = $attachment['name'];
            if (empty($filename)) $filename = $attachment['filename'];

            if (empty($filename)) $filename = time().".dat";

            echo($filename." ");

            if (file_exists($email_number."-".$filename)) {
                echo(".. file already exists! \n");
            } else {
                echo(".. saving \n");
                $fp = fopen('dwn/'.$email_number."-".$filename, "w+");
                fwrite($fp, $attachment['attachment']);
                fclose($fp);
            }
        }
    }
}

imap_close($inbox);

echo "Done";

