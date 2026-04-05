<?php
require '/app/vendor/autoload.php';
echo class_exists('Brevo\TransactionalEmails\Api\TransactionalEmailsApi') ? "OK" : "NOT FOUND";
?>
