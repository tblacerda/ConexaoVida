<?php

/**
 * Description of Empreendimento
 *
 * @author Alberto Medeiros
 */
class Contato {
    //put your code here
    public function post_contatoSite(){
        $name    = stripslashes(trim($_POST['name']));
        $email   = stripslashes(trim($_POST['email']));
        $subject = stripslashes(trim($_POST['subject']));
        $message = stripslashes(trim($_POST['message']));
        if (empty($name)) {
            $errors['name'] = 'Name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid.';
        }
        if (empty($subject)) {
            $errors['subject'] = 'Subject is required.';
        }
        if (empty($message)) {
            $errors['message'] = 'Message is required.';
        }
        // if there are any errors in our errors array, return a success boolean or false
        if (!empty($errors)) {
            $data['success'] = false;
            $data['errors']  = $errors;
        } else {
            // Criando o email
            $mail = new PHPMailer();
            $mail->IsSMTP();		// Ativar SMTP
            //$mail->SMTPDebug = 3;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
            $mail->SMTPAuth = true;		// Autenticação ativada
            $mail->SMTPSecure = 'ssl';	// SSL REQUERIDO pelo GMail
            $mail->Host = 'smtp.gmail.com';	// SMTP utilizado
            $mail->Port = 465;  		// A porta 587 deverá estar aberta em seu servidor
            $mail->SMTPSecure = 'ssl';
            $mail->Username = 'conexaovidaimip@gmail.com';
            $mail->Password = 'conexaovida';
            $mail->FromName = 'Conexão Vida - IMIP';
            $mail->IsHTML(true);
            // Caso seja um único email
            $mail->addAddress("infinitympes@gmail.com");
            // Add a recipient
            $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
            $mail->Subject = "Contato Site";
            // Criando o corpo do email
            $strBody = "<strong>Name: </strong>'.$name.'<br />$subject
                        <strong>Assunto: </strong>'.$subject.'<br />
                        <strong>Email: </strong>'.$email.'<br />
                        <strong>Message: </strong>'.nl2br($message).'<br />";
            // Colocando o corpo do email
            $mail->Body = $strBody;
            
            try {
                if(!$mail->send()) throw new Exception("Não foi possível enviar e-mail!", 9999);
                $data = 1;
                
            } catch (Exception $e) {
                $data = 0;
            }
        }
        return true;
    }
}
