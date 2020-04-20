<?php

namespace Controller\Traits;

trait InboxSendMessageTrait
{

    private function sendMessageValidator($requiredFields) {
        $errors = [];
        foreach ($requiredFields as $field) {
            $_POST[$field] = trim($_POST[$field]);
            if (empty($_POST[$field])) {
                $errors[$field] = true;
            }
        }

        return $errors;
    }

    private function enqueueEmails($mailer, $emailFields) {
        $emailValues = [];

        foreach ($emailFields as $field) {
            if (empty($_POST[$field]) || empty(trim($_POST[$field]))) {
                continue;
            }

            $values = explode(',', $_POST[$field]);
            foreach ($values as $value) {
                $email = trim($value);
                $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
                $inArray = !empty($emailValues[$field]) && in_array($email, $emailValues[$field]);

                if ($isEmail && !$inArray) {
                    switch ($field) {
                        case 'to':
                            $mailer->addAddress($email);
                            break;
                        case 'cc':
                            $mailer->addCC($email);
                            break;
                        case 'bcc':
                            $mailer->addBCC($email);
                            break;
                        case 'reply':
                            if (empty($emailValues[$field])) {
                                $mailer->addReplyTo($email, $_POST['nameFrom']);
                            }
                            break;
                        default:
                            break;
                    }

                    $emailValues[$field][] = $email;
                }
            }
        }

        return $emailValues;
    }

    private function addEmailsToMailer($mailer) {
        $errors = [];
        $emailFields = ['to', 'reply', 'emailFrom', 'cc', 'bcc'];
        $requiredEmailFields = ['to', 'reply', 'emailFrom'];
        $emailValues = $this->enqueueEmails($mailer, $emailFields);

        foreach ($requiredEmailFields as $field) {
            if (empty($emailValues[$field])) {
                $errors[$field] = true;
            }
        }

        if (count($errors)) {
            return ['errors' => $errors];
        }

        return $mailer;
    }
}
