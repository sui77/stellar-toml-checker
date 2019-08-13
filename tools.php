<?php

function getStellarTomlHttp($url, $ignoreSsl = false)
{
    $return = [
        'header' => [],
        'body' => '',
        'errors' => [],
        'redirects' => [],
    ];
    do {
        $redirect = false;
        $return['header'] = [];
        $return['body'] = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_USERAGENT,'stellar.toml checker https://stellar.sui.li/toml-check');

        if ($ignoreSsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        if (!($response = curl_exec($ch))) {
            $return['errors'][] = '' . curl_error($ch);
            return $return;
        }

        if (!preg_match('/^HTTP/i', $response)) {
            $return['errors'][] = 'Not a http response (' . substr(trim(explode("\n", $response)[0]), 0, 50) . ')';
        }

        $tmp = explode("\r\n\r\n", $response);

        $header = parseHttpHeader(array_shift($tmp));

        $return['header'] = $header;
        $return['body'] = implode("\r\n\r\n", $tmp);

        if ($header['xhttp_code'] == 301 || ($header['xhttp_code'] == 302)  || ($header['xhttp_code'] == 307)  || ($header['xhttp_code'] == 308)) {
            $return['redirects'][] = $header['location'];
            $url = $header['location'];
            $redirect = true;
        } elseif ($header['xhttp_code'] != 200) {
            $return['errors'][] = 'Server response: ' . $header['xhttp_code'] . ' ' . $header['xhttp_status'];
        }
    } while ($redirect !== false && count($return['redirects']) < 5);

    if ($redirect) {
        $return['errors'][] = 'Too many redirects.';
    }
    return $return;
}

function parseHttpHeader($header)
{
    $return = array();
    $lines = explode("\n", $header);
    $first = array_shift($lines);
    $tmp = explode(' ', $first, 3);

    $return['xhttp_version'] = $tmp[0];
    $return['xhttp_code'] = $tmp[1];
    $return['xhttp_status'] = $tmp[2];
    foreach ($lines as $line) {

        $tmp = explode(':', $line, 2);
        $return[strtolower(trim($tmp[0]))] = trim($tmp[1]);
    }
    return $return;
}

function output($title, $status, $text)
{
    if ($status == 'success') {
        $bg = 'bg-success';
        $icon = 'fas fa-thumbs-up';
    } else if ($status == 'failed') {
        $bg = 'bg-danger';
        $icon = 'fas fa-exclamation-triangle';
    } else if ($status == 'warning') {
        $bg = 'bg-warning';
        $icon = 'fas fa-info-circle';
    }
    return '
        <div class="row">
        <div class="d-flex align-items-center col-md-2 ' . $bg . ' text-light text-center">
            <div style="width:100%;text-align:center">
            <i style="font-size:2em" class="' . $icon . '"></i>
            <br>' . $status . '
            </div>
        </div>
        <div class="content col-md-10 bg-grey">
            ' . $text . '
        </div>
    </div>';

}