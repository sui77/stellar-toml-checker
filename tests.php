<?php

function retrieveHttpTest($domain)
{
    $url = 'http://' . $domain . '/.well-known/stellar.toml';
    $r =  getStellarTomlHttp($url, 1);
    $r['info'] = '<strong>Fetch NON-SSL test:</strong> <a href="' . $url . '">' . $url . '</a>';
    return $r;
}

function retrieveHttpsTest($domain)
{
    $url = 'https://' . $domain . '/.well-known/stellar.toml';
    $r =  getStellarTomlHttp($url);
    $r['info'] = '<strong>Fetch SSL test:</strong> <a href="' . $url . '">' . $url . '</a>';
    return $r;
}

function retrieveHttpsIgnoreTest($domain)
{
    $url = 'https://' . $domain . '/.well-known/stellar.toml';
    $r =  getStellarTomlHttp($url, true);
    $r['info'] = 'GET ' . $url;
    return $r;
}