<?php
require '/app/vendor/autoload.php';
echo class_exists('Brevo\Client\Configuration') ? "Brevo OK" : "Brevo NOT FOUND";
?>
