<?php 
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>