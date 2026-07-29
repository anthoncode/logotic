<?php
echo "extension_dir: " . ini_get('extension_dir') . "<br>";
echo "Existe imagick ahí: " . (file_exists(ini_get('extension_dir') . '/imagick.so') ? 'SÍ' : 'NO');