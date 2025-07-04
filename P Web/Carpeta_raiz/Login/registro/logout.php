<?php
session_start();
session_destroy();
header("Location: ../../PAGINA/index.php");
exit();
