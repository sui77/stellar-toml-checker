<?php

/* Don't judge the code, I did it casually in a couple of nights and I know it's not clean & hacky */

use Yosymfony\Toml\Toml;
include 'TomlContentChecker.class.php';
            ini_set('default_socket_timeout', 20);
ini_set('display_errors', 0);

require 'vendor/autoload.php';?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>stellar.toml checker</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        .jumbotron {
            padding: 2em;
        }

        .bg-grey {
            background-color: #e9ecef;
        }

        #main .row {
            margin-bottom: 0.5em;
        }

        .tag {
            padding: auto;
        }

        .content {
            padding: 10px;
        }

        .hljs-ln-line {
            margin-right: 12px;
        }

    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.14.2/styles/a11y-dark.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.14.2/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlightjs-line-numbers.js/2.6.0/highlightjs-line-numbers.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.14.2/languages/ini.min.js"></script>

    <script>
        $(function () {
            hljs.initHighlighting();
            hljs.initLineNumbersOnLoad();
            $('#submitForm').submit(function(e) {
                e.preventDefault();
                $('#submitButton').attr('disabled', 'disabled');
                top.location.href='https://stellar.sui.li/toml-check/' + $('#home_domain').val() ;

                window.setInterval( function() {
                    let b = $('#submitButton').html();
                    if (b.length > 8) {
                        b = 'Check';
                    } else {
                        b+='.';
                    }
                    $('#submitButton').html(b);
                    }, 500);

            });
        });

    </script>

</head>

<body>

<?php $page = 'tomlcheck'; include('../navbar.inc.php'); ?>

<div id="main" class="container">
    <div class="jumbotron">
        <div class="container">
            <h2>stellar.toml checker</h2>
            Checks your <a href="https://github.com/stellar/stellar-protocol/blob/master/ecosystem/sep-0001.md">stellar.toml</a> for connectivity, syntax, valid contents and availability of defined services and assets.
        </div>
    </div>

        <form method="post" action="/toml-check" id="submitForm">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text">home_domain or accountID</div>
                        </div>
                        <input value="<?= (isset($_REQUEST['home_domain']) ? htmlspecialchars($_REQUEST['home_domain']) : '') ?>" autocomplete="off" name="home_domain" type="text" class="form-control" id="home_domain" placeholder="">
                    </div>
                </div>
                <div class="col-auto">
                    <button id="submitButton" type="submit" class="btn btn-secondary mb-2">Check</button>
                </div>
            </div>
        </form>


    <hr>
<div class="container">
    <?php
    ini_set('display-errors', 1);
    if (isset($_REQUEST['home_domain'])) {
        include 'tools.php';
        include 'tests.php';

        $fp = fopen('slog.txt', 'a');
        fputs($fp, date('Y-m-d H:i:s') . ' ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . ' ' . json_encode($_REQUEST) . "===\n");
        fclose($fp);

        $domain = $_REQUEST['home_domain'];
        $domainfail = false;
        if (preg_match('/^G[A-Z0-9]{55}$/', $_REQUEST['home_domain'])) {
            $acc = json_decode(file_get_contents('http://127.0.0.1:8000/accounts/' . $_REQUEST['home_domain']), 1);

            $_REQUEST['home_domain'] = $domain = $acc['home_domain'];
            if (empty($domain)) {
                $domainfail = true;
                echo output('domain', 'failed', '<strong>Not found</strong><hr>No home_domain found for account ' . htmlspecialchars($_REQUEST['home_domain']) . '');
            }
        }
        
        if ($domainfail) {
        } elseif (preg_match('/[<>"\']/si', $domain) || empty($domain)) {
            echo output('domain', 'failed', '<strong>Invalid Domain</strong><hr>This is not a valid domain.');

        } else {

            $httpResult = retrieveHttpTest($domain, true);
            $httpsResult = retrieveHttpsTest($domain);
            $httpsIgnoreResult = retrieveHttpsIgnoreTest($domain);
            if (count($httpsIgnoreResult['redirects']) > 0) {
                $lastUrl = array_pop($httpsIgnoreResult['redirects']);
            } else {
                $lastUrl = 'https://' . $domain . '/.well-known/stellar.toml';
            }


            $text = $httpsResult['info'] . '<hr>';
            if ($httpsIgnoreResult['body'] == '') {
                $httpsIgnoreResult['errors'][] = 'Did not get any content';
            }
            if (count($httpsResult['errors']) > 0) {
                $httpsResult['errors'] = array_unique(array_merge($httpsResult['errors'], $httpsIgnoreResult['errors']));
                $status = 'failed';
            } else {
                $status = 'success';
                $ct = $httpsIgnoreResult['header']['content-type'];
                if (!preg_match('/^text\/plain|application\/toml/', $ct)) {
                    $text .= '<strong>Info:</strong><ul><li>Recommended content-type is text/plain or application/toml (got: ' . $ct . ').</li></ul>';
                };
                if (count($httpsResult['redirects']) > 1) {
                    $status = 'failed';
                    $httpsResult['errors'][] = 'Too many redirects.';
                } elseif (count($httpsResult['redirects']) == 1) {
                    $status = 'warning';
                    $httpsResult['errors'][] = 'The endpoint redirects once, it would be better if served directly.';
                } else {
                    $text .= 'No issues found.';
                }
            }
            if (count($httpsResult['errors']) > 0) {
                $text .= '<strong>Errors: </strong><ul><li>' . implode('</li><li>', $httpsResult['errors']) . '</li></ul>';
            }
            if (count($httpsResult['redirects']) > 0) {
                $text .= '<strong>Redirects: </strong><ul><li>' . implode('</li><li>', $httpsResult['redirects']) . '</li></ul>';
            }
            echo output('https', $status, $text);


            $text = '';
            if (count($httpResult['redirects']) == 1 && $httpResult['redirects'][0] == 'https://' . $domain . '/.well-known/stellar.toml') {
                $status = count($httpResult['errors']) == 0 ? 'success' : 'warning';
                $text .= 'Your http endpoint redirects to your https endpoint.';
            } elseif ($httpResult['body'] == $httpsIgnoreResult['body']) {
                $status = count($httpResult['errors']) == 0 ? 'success' : 'warning';
                $text .= 'Your http endpoint serves the same content as your https endpoint.';
                if ($httpResult['body'] == '') {
                    $httpResult['errors'][] = 'Did not get any content.';
                }
            } elseif ($httpResult['body'] != $httpsIgnoreResult['body']) {
                $status = 'failed';
                $text .= 'Your http endpoint serves a different content compared to https.';
            } else {
                $status = 'warning';
            }
            $text .= '<br><br>';

            $text = $httpResult['info'] . '<hr>' .
                ($status == 'success' ? '' : 'Altough stellar.toml is supposed to be accessed via https only, it is best practice to either redirect your traffic from http to https or serve the same content.<br><br>') .
                $text;


            if (count($httpResult['redirects']) > 1) {
                $status = 'warning';
                $httpResult['errors'][] = 'Too many redirects.';
            }
            if (count($httpResult['errors']) > 0) {
                $text .= '<strong>Errors: </strong><ul><li>' . implode('</li><li>', $httpResult['errors']) . '</li></ul>';
            }


            if (count($httpResult['redirects']) > 0) {
                $text .= '<strong>Redirects: </strong><ul><li>' . implode('</li><li>', $httpResult['redirects']) . '</li></ul>';
            }
            echo output('http', $status, $text);

            $text = '<strong>CORS test</strong><hr>';
            if (isset($httpsIgnoreResult['header']['access-control-allow-origin'])) {
                if ($httpsIgnoreResult['header']['access-control-allow-origin'] == '*') {
                    $status = 'success';
                    $text .= 'Your CORS header is set correctly.';
                } else {
                    $text .= 'Your CORS header is set to <b>' . htmlspecialchars($httpsIgnoreResult['header']['access-control-allow-origin']) . '</b>. You have to change it to <b>*</b> to allow access to every client. [<a href="https://enable-cors.org" target="_blank">how to</a>]';
                    $status = 'warning';
                }
            } else {
                $status = 'failed';
                $text .= 'Your CORS header is not set. You have to set it to <b>*</b> to allow access to every client. [<a href="https://enable-cors.org" target="_blank">how to</a>]';
            }

            echo output('cors', $status, $text);

            $text = '<strong>Parse TOML test:</strong> <a href="' . $lastUrl . '">' . $lastUrl . '</a><hr>';
            $toml = $httpsIgnoreResult['body'];

            $errors = [];
            try {
                $array = Toml::Parse($toml);
                $status = 'success';
            } catch (Exception $e) {
                $status = 'failed';
                if (preg_match('/line ([0-9]+)/si', $e->getMessage(), $m)) {
                    echo '<script>

jQuery.fn.scrollTo = function(elem) { 
    $(this).scrollTop($(this).scrollTop() - $(this).offset().top + $(elem).offset().top); 
        return this; 
};

$(function() {
    window.setTimeout( () => {
    $("div[data-line-number=' . $m[1] . ']").parent().parent().addClass("bg-danger");
    $("#source").scrollTo( "div[data-line-number=' . ($m[1] - 4 > 0 ? $m[1] - 4 : 1) . ']");
                
    }, 1000);
    }); 
    
    </script>';
                }
                $errors[] = '' . $e->getMessage();
            }
            if (empty(trim($toml))) {
                $status = 'failed';
                $errors[] = 'Your stellar.toml is empty.';
            } else {
                $text .= 'Source<pre><code id="source" style="overflow:auto;height:300px;" class="ini">';
                $text .= htmlspecialchars($toml);
                $text .= '</code></pre>';
            }
            if (count($errors) > 0) {
                $text .= '<strong>Errors: </strong><ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
            }
            echo output('http', $status, $text);

        }

        if ($status == 'success') {
            $tCheck = new TomlContentChecker($array);
            $r = $tCheck->checkTree();
            $text = '<strong>stellar.toml global vars</strong><hr>';
            if (count($r) == 0) {
                $text .= 'No issues found.';
            } else {
                $text .= '<strong>Warnings: </strong><ul><li>' . implode('</li><li>', $r) . '</li></ul>';
            }
            echo output("", count($r) > 0 ? 'warning' : 'success', $text);

            if (isset($array['DOCUMENTATION'])) {
                $r = $tCheck->checkTree('DOCUMENTATION');
                $text = '<strong>stellar.toml DOCUMENTATION section</strong><hr>';
                if (count($r) == 0) {
                    $text .= 'No issues found.';
                } else {
                    $text .= '<strong>Warnings: </strong><ul><li>' . implode('</li><li>', $r) . '</li></ul>';
                }
                echo output("", count($r) > 0 ? 'warning' : 'success', $text);
            }

            if (isset($array['PRINCIPALS'])) {
                $r = $tCheck->checkTree('PRINCIPALS', true);
                $text = '<strong>stellar.toml PRINCIPALS section</strong><hr>';
                if (count($r) == 0) {
                    $text .= 'No issues found.';
                } else {
                    $text .= '<strong>Warnings: </strong><ul><li>' . implode('</li><li>', $r) . '</li></ul>';
                }
                echo output("", count($r) > 0 ? 'warning' : 'success', $text);
            }

            if (isset($array['CURRENCIES'])) {
                $r = $tCheck->checkTree('CURRENCIES', true);
                $text = '<strong>stellar.toml CURRENCIES section</strong><hr>';
                if (count($r) == 0) {
                    $text .= 'No issues found.';
                } else {
                    $text .= '<strong>Warnings: </strong><ul><li>' . implode('</li><li>', $r) . '</li></ul>';
                }
                echo output("", count($r) > 0 ? 'warning' : 'success', $text);
            }

            if (isset($array['VALIDATORS'])) {
                $r = $tCheck->checkTree('VALIDATORS', true);
                $text = '<strong>stellar.toml VALIDATORS section</strong><hr>';
                if (count($r) == 0) {
                    $text .= 'No issues found.';
                } else {
                    $text .= '<strong>Warnings: </strong><ul><li>' . implode('</li><li>', $r) . '</li></ul>';
                }
                echo output("", count($r) > 0 ? 'warning' : 'success', $text);
            }

            if (isset($array['QUORUM_SET'])) {
                $r = $tCheck->checkTree('QUORUM_SET', false);
                $text = '<strong>stellar.toml QUORUM_SET section</strong><hr>';
                if (count($r) == 0) {
                    $text .= 'No issues found.';
                } else {
                    $text .= '<strong>Warnings: </strong><ul><li>' . implode('</li><li>', $r) . '</li></ul>';
                }
                echo output("", count($r) > 0 ? 'warning' : 'success', $text);
            }
        }


    }
    ?>


</div>
</div>



</body>
</html>
