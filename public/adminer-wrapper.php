<?php
function adminer_object() {
    class AdminerSoftware extends Adminer {
        function name() {
            return 'Your Database Name';
        }
    }
    return new AdminerSoftware;
}

include_once 'adminer.php';
