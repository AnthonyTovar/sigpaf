<?php
class SecurityHelper {
    public static function preventBackAfterLogout() {
        // No permite guardar copia
        header("Cache-Control: no-cache, no-store, must-revalidate"); 
        header("Pragma: no-cache"); 
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
    }
}