<?php
echo "php.ini de Apache: " . php_ini_loaded_file() . "<br>";
echo "Imagick cargado: " . (extension_loaded('imagick') ? 'SÍ' : 'NO') . "<br>";
echo "PHP version: " . phpversion();