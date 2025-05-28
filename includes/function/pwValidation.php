<?php
function isStrongPassword($password) {
    // Minimal 6 karakter
    if (strlen($password) < 6) {
        return false;
    }
    
    // Harus mengandung huruf besar, huruf kecil, angka, dan simbol
    if (!preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[^A-Za-z0-9]/', $password)) {
        return false;
    }
    
    return true;
}
?>