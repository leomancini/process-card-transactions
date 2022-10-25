<?php
    $PATH = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]".dirname($_SERVER['PHP_SELF']);

    function sortBySimilarity($a, $b) {
        return $a['similarity'] - $b['similarity'];
    }

    function filterOutLowSimilarity($list) {
        return $list['similarity'] < 5;
    }
?>