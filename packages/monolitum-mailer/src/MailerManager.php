<?php

namespace monolitum\mailer;

use Closure;
use monolitum\core\MNode;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailerManager extends MNode
{

    /**
     * @var MailCredentials[]
     */
    private array $mailCredentials = [];

    /**
     * @var SMTP[]
     */
    private array $smtps = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function addMailCredentials(string $name, MailCredentials $mailCredentials): self
    {
        $this->mailCredentials[$name] = $mailCredentials;
        return $this;
    }

    public function createNewMail(string $keyname): PHPMailer
    {

        if (array_key_exists($keyname, $this->mailCredentials)) {

            $mailCredentials = $this->mailCredentials[$keyname];

            if (!array_key_exists($keyname, $this->smtps) || !$this->smtps[$keyname]->connected()) {
                $smtp = new SMTP();
//
//                if(!$smtp->connect($mailCredentials->getHost(), 587)){
//                    throw new MailPanic($smtp->getError());
//                }
//                $smtp->startTLS();
//                if(!$smtp->authenticate($mailCredentials->getAddress(), $mailCredentials->getPassword())){
//                    throw new MailPanic($smtp->getError());
//                }

                $this->smtps[$keyname] = $smtp;
            }else{
                $smtp = $this->smtps[$keyname];
            }

            $phpMailer = new PHPMailer();
            $phpMailer->setSMTPInstance($smtp);

            try {

                // SMTP Configuration
                $phpMailer->isSMTP();
                $phpMailer->Host = $mailCredentials->host;
                $phpMailer->SMTPAuth = true;
                $phpMailer->Username = $mailCredentials->address;
                $phpMailer->Password = $mailCredentials->password;
                $phpMailer->SMTPSecure = 'tls';
                $phpMailer->Port = 587;

                $phpMailer->setFrom($mailCredentials->address, $mailCredentials->name);
            } catch (Exception $e) {
                throw new MailPanic($smtp->getError());
            }

            return $phpMailer;

        }else{
            throw new MailPanic();
        }

    }

}
