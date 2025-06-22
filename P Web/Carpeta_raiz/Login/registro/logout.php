<?php
session_start();
session_destroy();

// Redirigir al archivo correcto (index.htm, no .html)
header("Location: ../../PAGINA/index.php");
exit();
