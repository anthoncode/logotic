<?php
class Mailer
{

    private $db;
    private $setting;

    public function __construct($DB_con, $setting)
    {
        $this->db      = $DB_con;
        $this->setting = $setting;
    }

    public function send($to, $subject, $body)
    {
        // Usar PHPMailer si SMTP está habilitado
        if ($this->setting['smtp_enabled'] == '1') {
            return $this->sendSMTP($to, $subject, $body);
        }
        return $this->sendMail($to, $subject, $body);
    }

    private function sendMail($to, $subject, $body)
    {
        $fromName  = $this->setting['smtp_from_name']  ?: $this->setting['site_name'];
        $fromEmail = $this->setting['smtp_from_email'] ?: 'noreply@' . $_SERVER['HTTP_HOST'];
        $headers   = "MIME-Version: 1.0\n";
        $headers  .= "From: {$fromName} <{$fromEmail}>\n";
        $headers  .= "Content-type: text/html; charset=utf-8\n";
        return mail($to, $subject, $body, $headers);
    }

    private function sendSMTP($to, $subject, $body)
    {
        // class.mailer.php está en system/classes/
        // vendor/ está en la raíz del proyecto
        // Necesitamos subir: classes/ → system/ → logotic/ (raíz)
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';

        error_log('Looking for autoload at: ' . $autoload); // debug temporal

        if (!file_exists($autoload)) {
            error_log('PHPMailer not installed. Falling back to mail().');
            return $this->sendMail($to, $subject, $body);
        }

        require_once $autoload;
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

         // ← AGREGA AQUÍ el log
    error_log('SMTP Config — Host: ' . $this->setting['smtp_host'] 
        . ' | Port: ' . $this->setting['smtp_port']
        . ' | Enc: ' . $this->setting['smtp_encryption']
        . ' | User: ' . $this->setting['smtp_user']
        . ' | From: ' . $this->setting['smtp_from_email']);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->setting['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->setting['smtp_user'];
            $mail->Password   = $this->setting['smtp_pass'];
            $mail->SMTPSecure = $this->setting['smtp_encryption'] === 'ssl'
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$this->setting['smtp_port'];
            $mail->setFrom(
                $this->setting['smtp_from_email'] ?: 'noreply@' . $_SERVER['HTTP_HOST'],
                $this->setting['smtp_from_name']  ?: $this->setting['site_name']
            );
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->CharSet = 'UTF-8';
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }


    public function template($title, $content, $btnText = null, $btnUrl = null)
    {
        $siteName = $this->setting['site_name'];
        $siteUrl  = $this->setting['website_url'];
        $logo     = $siteUrl . '/system/assets/uploads/img/' . $this->setting['site_favicon'];
        $btn      = $btnText && $btnUrl
            ? "<a href='{$btnUrl}' style='display:inline-block;margin-top:1.25rem;background:#d4ff00;color:#0d0f1c;padding:.65rem 1.75rem;border-radius:99px;text-decoration:none;font-weight:700;font-size:.9rem;'>{$btnText}</a>"
            : '';

        return "
        <html><head><meta charset='utf-8'></head>
        <body style='margin:0;padding:0;background:#0d0f1c;font-family:sans-serif;'>
            <div style='max-width:520px;margin:2rem auto;background:#13152a;border:1px solid rgba(255,255,255,.08);border-radius:14px;overflow:hidden;'>
                <div style='background:#0d0f1c;padding:1.5rem;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);'>
                    <img src='{$logo}' height='36' alt='{$siteName}'>
                </div>
                <div style='padding:2rem;text-align:center;'>
                    <h2 style='color:#d4ff00;margin:0 0 .5rem;font-size:1.2rem;'>{$title}</h2>
                    <div style='color:#8b8fa8;font-size:.88rem;line-height:1.6;'>
                        {$content}
                    </div>
                    {$btn}
                </div>
                <div style='padding:1rem 2rem;text-align:center;border-top:1px solid rgba(255,255,255,.08);'>
                    <p style='color:#8b8fa8;font-size:.72rem;margin:0;'>
                        &copy; " . date('Y') . " {$siteName}. If you didn't request this, ignore this email.
                    </p>
                </div>
            </div>
        </body></html>";
    }
}
