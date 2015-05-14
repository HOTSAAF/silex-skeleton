<?php

$publicFolderNames = array(
    'web', // Sf2, Silex
    'public', // Laravel
    'upload', // OpenCart
);

foreach ($publicFolderNames as $folderName) {
    if (is_dir($folderName)) {
        header('location: ' . $folderName);
        exit;
    }
}
