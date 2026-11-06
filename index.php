<?php
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
header("Location: " . $base . "/public/" . $queryString);
exit;
