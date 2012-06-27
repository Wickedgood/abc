<?php
session_start();
session_unset();
session_destroy();
echo '<script language="Javascript">';
echo 'window.location="http://ud3a.com/"';
echo '</script>';	
?>