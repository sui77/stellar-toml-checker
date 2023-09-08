<?php

use ParagonIE\Sodium\Core\Ed25519;

class TomlContentChecker
{
    protected $curlResult = [];

    protected $config = [
        'root' => [
            'VERSION' => [
                'version',
            ],
            'HORIZON_URL' => [
                'horizonServer'
            ],
            'NETWORK_PASSPHRASE' => [
                'string'
            ],
            'FEDERATION_SERVER' => [
                'federationServer:json'
            ],
            'AUTH_SERVER' => [
                'deprecatedSep3'
            ],
            'DEPOSIT_SERVER' => [
                'depositServer'
            ],
            'TRANSFER_SERVER' => [
                'transferServer'
            ],
            'TRANSFER_SERVER_SEP0024' => [
                'transferServer'
            ],
            'KYC_SERVER' => [
                'https'
            ],
            'WEB_AUTH_ENDPOINT' => [
                'webauthServer'
            ],
            'SIGNING_KEY' => [
                'publicKey'
            ],
            'NODE_NAMES' => [
                'nodenamesArray',
                'deprecated200'
            ],
            'ACCOUNTS' => [
                'pubkeyArray'
            ],
            'OUR_VALIDATORS' => [
                'ourValidators',
                'deprecated200'
            ],
            'ASSET_VALIDATOR' => [
                'publicKey',
                'deprecated200',
            ],
            'DESIRED_BASE_FEE' => [
                'int',
                'deprecated200',
            ],
            'DESIRED_MAX_TX_PER_LEDGER' => [
                'int',
                'deprecated200',
            ],
            'KNOWN_PEERS' => [
                'stringArray',
                'deprecated200',
            ],
            'HISTORY' => [
                'stellarHistory',
                'deprecated200',
            ],
            'URI_REQUEST_SIGNING_KEY' => [
                'publicKey'
            ],
            'DIRECT_PAYMENT_SERVER' => [
                'https'
            ],
            'ANCHOR_QUOTE_SERVER' => [
                'https'
            ],
            'DOCUMENTATION' => [
            ],
            'PRINCIPALS' => [
                'tomlTable'
            ],
            'CURRENCIES' => [
            ],
            'QUORUM_SET' => [
                'deprecated200',
            ],
            'VALIDATORS' => [
            ],
        ],
        'DOCUMENTATION' => [

            'ORG_NAME' => [
                'string'
            ],
            'ORG_DBA' => [
                'string'
            ],
            'ORG_URL' => [
                'https'
            ],
            'ORG_LOGO' => [
                'curl'
            ],
            'ORG_DESCRIPTION' => [
                'string'
            ],
            'ORG_PHYSICAL_ADDRESS' => [
                'string'
            ],
            'ORG_PHYSICAL_ADDRESS_ATTESTATION' => [
                'https'
            ],
            'ORG_PHONE_NUMBER' => [
                'phone'
            ],
            'ORG_PHONE_NUMBER_ATTESTATION' => [
                'https'
            ],
            'ORG_KEYBASE' => [
                'string'
            ],
            'ORG_TWITTER' => [
                'string'
            ],
            'ORG_GITHUB' => [
                'string'
            ],
            'ORG_OFFICIAL_EMAIL' => [
                'string'
            ],
            'ORG_SUPPORT_EMAIL' => [
                'string'
            ],
            'ORG_LICENSING_AUTHORITY' => [
                'string'
            ],
            'ORG_LICENSE_TYPE' => [
                'string'
            ],
            'ORG_LICENSE_NUMBER' => [
                'string'
            ],
        ],
        'PRINCIPALS' => [
            'name' => [
                'string'
            ],
            'email' => [
                'string'
            ],
            'keybase' => [
                'string'
            ],
            'telegram' => [
                'string'
            ],
            'twitter' => [
                'string'
            ],
            'github' => [
                'string'
            ],
            'id_photo_hash' => [
                'string'
            ],
            'verification_photo_hash' => [
                'string'
            ],
        ],
        'CURRENCIES' => [
            'code' => [
                'assetCode'
            ],
            'code_template' => [
                'assetCode'
            ],
            'issuer' => [
                'account'
            ],
            'status' => [
                'enum:live,dead,test,private'
            ],
            'display_decimals' => [
                'enum:0,1,2,3,4,5,6,7'
            ],
            'name' => [
                'string:20'
            ],
            'desc' => [
                'string'
            ],
            'conditions' => [
                'string'
            ],
            'image' => [
                'curl'
            ],
            'fixed_number' => [
                'int'
            ],
            'max_number' => [
                'int'
            ],
            'is_unlimited' => [
                'boolean'
            ],
            'is_asset_anchored' => [
                'boolean'
            ],
            'anchor_asset_type' => [
                'enum:fiat,crypto,nft,stock,bond,commodity,realestate,other'
            ],
            'anchor_asset' => [
                'string'
            ],
            'attestation_of_reserve' => [
                'https'
            ],
            'redemption_instructions' => [
                'string'
            ],
            'collateral_addresses' => [
                'arrayStrings'
            ],
            'collateral_address_messages' => [
                'arrayStrings'
            ],
            'collateral_address_signatures' => [
                'arrayStrings'
            ],
            'regulated' => [
                'boolean'
            ],
            'approval_server' => [
                'https'
            ],
            'approval_criteria' => [
                'string'
            ],
        ],
        'VALIDATORS' => [
            'ALIAS' => [
                'alphanum',
            ],
            'DISPLAY_NAME' => [
                'string',
            ],
            'HOST' => [
                'hostname',
            ],
            'PUBLIC_KEY' => [
                'isValidator:true',
            ],
            'HISTORY' => [
                'history',
            ],
        ],
        'QUORUM_SET' => [
            'VALIDATORS' => [
                'pubkeyArray',
            ],


        ]
    ];

    protected $nodeNames = [];
    protected $stellarbeat;

    function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function alphanumTest($s)
    {
        if (!preg_match('/^[a-z0-9-]{2,16}$/', $s)) {
            return 'Invalid characters or length, [a-z 0-9 -] only, 2 to 16 characters.';
        }
    }

    public function depositServerTest($s)
    {
        return 'deprecated, use TRANSFER_SERVER instead.';
    }

    public function tomlTableTest($s)
    {
        if ($this->isAssoc($s)) {
            return 'Must be a TOML array of tables (double brackets)';
        }
    }

    public function deprecatedSep3Test()
    {
        return 'SEP-0003 is deprecated. https://github.com/stellar/stellar-protocol/blob/master/ecosystem/sep-0003.md';
    }

    public function deprecated200Test()
    {
        return 'Deprecated in SEP-1 v2.0.0';
    }

    public function __construct($array)
    {
        $this->array = $array;
        $this->loadStellarBeat();
    }

    private function loadStellarBeat()
    {
        $redis = new \Predis\Client();

        if (!($nodes = $redis->get('xstellarbeat'))) {
            $sb = json_decode(file_get_contents('https://api.stellarbeat.io/v1/nodes'), 1);
            $nodes = [];
            foreach ($sb as $node) {
                if ($node['statistics']['active24HoursPercentage'] > 0) {
                    $nodes[] = $node['publicKey'];
                }
            }
            $redis->setex('stellarbeat', 60, json_encode($nodes));

        } else {
            $nodes = json_decode($nodes);
        }
        // print_r($nodes);
        $this->stellarbeat = $nodes;
    }

    public function versionTest($s)
    {
        return '';
    }

    public function phoneTest($s)
    {
        if (!preg_match('/^\+[0-9 ]*$/', $s)) {
            return 'Recomended phone format is E.164 (e.g. `+14155552671`)';
        }
    }

    public function assetCodeTest($s)
    {
        if (!preg_match('/^.{1,12}$/', $s)) {
            return $s . ' is not a valid asset code.';
        }
    }

    public function booleanTest($val)
    {
        if (!is_bool($val)) {
            return ' should be boolean (true or false without quotation marks).';
        }
    }

    public function accountTest($key)
    {
        try {
            $key = \ZuluCrypto\StellarSdk\Keypair::newFromPublicKey($key);
        } catch (Exception $e) {
            return 'Invalid PublicKey: ' . $key;
        }
    }

    public function arrayStringsTest($val)
    {

    }

    protected function CURRENCIESTest($currency)
    {
        if (!isset($currency['code'])) {
            return 'Missing "code" attribute.';
        }
        if (!isset($currency['issuer'])) {
            return 'Missing "issuer" attribute.';
        }
        if ($pct = $this->publicKeyTest($currency['issuer'])) {
            return $pct;
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, 'https://horizon.stellar.org/assets/?asset_issuer=' . $currency['issuer'] . '&asset_code=' . $currency['code']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'stellar.toml checker https://stellar.sui.li/toml-check');

        if ($response = curl_exec($ch)) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] != 200) {
                return 'Could not find asset in mainnet. (' . $currency['code'] . '-' . $currency['issuer'] . ')';
            } else {
                $issuer = json_decode(file_get_contents('https://horizon.stellar.org/accounts/' . $currency['issuer']), 1);
                if (!isset($issuer['home_domain'])) {
                    return '(onchain) Issuer ' . $currency['issuer'] . ' home_domain not set.';
                } elseif ($issuer['home_domain'] != $_REQUEST['home_domain']) {
                    return '(onchain) Issuer ' . $currency['issuer'] . ' has set home_domain = ' . htmlspecialchars($issuer['home_domain'] . ' (expected: ' . htmlspecialchars($_REQUEST['home_domain']) . ')');
                }


            }
        }


    }

    public function checkTree($testsKey = 'root', $multi = false, $inData = null)
    {
        $warnings = [];
        if ($testsKey == 'root') {
            $array = &$this->array;
        } else {
            $array = &$this->array[$testsKey];
        }
        if (isset($inData)) {
            $array = &$inData;
        }
        $block = null;
        if ($multi) {
            $block = $array;
        } else {

            $block = [$array];

        }

        foreach ($block as $num => $subBlock) {
            $blockMethod = $testsKey . 'Test';
            if (method_exists($this, $blockMethod)) {
                $result = $this->$blockMethod($subBlock);
                if (!empty($result)) {
                    if (!is_array($result)) {
                        $result = [$result];
                    }
                    foreach ($result as $l) {
                        $warnings[] = ($multi ? ' [' . $testsKey . ' #' . $num . '] ' : '') . ': ' . $l;
                    }
                }
            }

            foreach ($subBlock as $k => $value) {
                //echo $k . '<br>';
                if (!isset($this->config[$testsKey][$k])) {
                    if (isset($this->config['DOCUMENTATION'][$k])) {
                        $warnings[] = $k . ($multi ? ' [' . $testsKey . ' #' . $num . '] ' : '') . ": Should be in the [DOCUMENTATION] section. ";
                    } elseif ($testsKey == 'QUORUM_SET' && $inData === null) {
                        $w2 = $this->checkTree('QUORUM_SET', false, $value);
                        foreach ($w2 as $w2k) {
                            $warnings[] = 'QUORUM_SET.' . $k . ': ' . $w2k;
                        }
                    } else {
                        $warnings[] = $k . ($multi ? ' [' . $testsKey . ' #' . $num . '] ' : '') . ": Is not a standard key. ";
                    }
                } else {
                    foreach ($this->config[$testsKey][$k] as $check) {
                        $tmp = explode(':', $check);
                        $method = $tmp[0] . 'Test';
                        $params = $tmp[1] ?? '';
                        if (method_exists($this, $method)) {
                            $result = $this->$method($value, $params);
                            if (!empty($result)) {
                                if (!is_array($result)) {
                                    $result = [$result];
                                }
                                foreach ($result as $l) {
                                    $warnings[] = $k . ($multi ? ' [' . $testsKey . ' #' . $num . '] ' : '') . ': ' . $l;
                                }
                            }
                        } else {
                            $warnings[] = $k . ' ' . ($multi ? ' [' . $testsKey . ' #' . $num . '] ' : '') . $method . ' not implemented yet';
                        }
                    }
                }
            }
        }
        return $warnings;
    }

    protected function enumTest($needle, $haystack)
    {
        if (!in_array($needle, explode(',', $haystack))) {
            return $needle . ' is not a valid value. Must be in [' . $haystack . ']';
        }
    }

    protected function ourValidatorsTest($a)
    {
        $errors = [];
        foreach ($a as $key) {
            if ($valid = $this->publicKeyTest($key)) {
                $errors[] = $valid;
            } elseif ($isValidator = $this->isValidatorTest($key, true)) {
                $errors[] = $isValidator;
            }
        }
        return $errors;
    }

    protected function pubkeyArrayTest($a)
    {
        if (!is_array($a)) {
            return ' is a ' . gettype($a) . ' (array expected)';
        }
        $errors = [];
        foreach ($a as $key) {
            if ($valid = $this->publicKeyTest($key)) {
                $errors[] = $valid;
            }
        }
        return $errors;
    }

    protected function federationServerTest($url)
    {
        $result = $this->curl($url . '?q=tomltest*' . $_REQUEST['home_domain'] . '&type=name');
        if ($result['error']) {
            return $result['error'];
        }

        if (!preg_match('/^application\/json/', $result['info']['content_type'])) {
            return 'Got ' . $result['info']['content_type'] . ' (expected application/json)';
        }

    }

    protected function horizonServerTest($url)
    {

        $result = $this->curl($url );
        $tmp = explode("\r\n\r\n", $result['body']);
        $tmp = json_decode($tmp[1], 1);

        if (!isset($tmp['horizon_version'])) {
            return 'Not available or unexpected result';
        }

    }

    protected function transferServerTest($url)
    {
        $result = $this->curl($url . '/info');
        if ($result['error']) {
            return $result['error'];
        }

        if (!preg_match('/^application\/json/', $result['info']['content_type'])) {
            return 'Got ' . $result['info']['content_type'] . ' (expected application\json)';
        }

    }

    protected function webauthServerTest($url)
    {
        $result = $this->curl($url . '?account=GBHMXTHDK7R2IJFUIDIUWMR7VAKKDSIPC6PT5TDKLACEAU3FBAR2XSUI');
        if ($result['error']) {
            return $result['error'];
        }

        if (!preg_match('/^application\/json/', $result['info']['content_type'])) {
            return 'Got ' . $result['info']['content_type'] . ' (expected application\json)';
        }

    }

    protected function authServerTest($url)
    {
        $result = $this->curl($url . '');
        if ($result['error']) {
            return $result['error'];
        }

        if (!preg_match('/^application\/json/', $result['info']['content_type'])) {
            return 'Got ' . $result['info']['content_type'] . ' (expected application\json)';
        }

    }

    protected function stringArrayTest($a)
    {
        if (!is_array($a)) {
            return ' is a ' . gettype($a) . ' (array expected)';
        }
    }

    protected function httpsTest($url)
    {
        if (!preg_match('/^https:/', $url)) {
            return 'Not a https URL';
        } else {
            return $this->curlTest($url);
        }
    }

    protected function stringTest($s)
    {
        return !is_string($s) ? 'Is not a string.' : '';
    }


    protected function curl($url)
    {
        $error = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'stellar.toml checker https://stellar.sui.li/toml-check');

        if (!($response = curl_exec($ch))) {
            $error = curl_error($ch);
        }
        $info = curl_getinfo($ch);

        if (!preg_match('/^HTTP/i', $response)) {
            $error = 'Not a http response (' . substr(trim(explode("\n", $response)[0]), 0, 50) . ')';
        }
        return [
            'info' => $info,
            'error' => $error,
            'body' => $response,
        ];
    }


    protected function curlTest($url)
    {
        if (isset($this->curlResult[$url])) {
            return $this->curlResult[$url];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'stellar.toml checker https://stellar.sui.li/toml-check');
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!($response = curl_exec($ch))) {
            return curl_error($ch);
        }
        $info = curl_getinfo($ch);

        if (!in_array($info['http_code'], [200])) {
            $this->curlResult[$url] =  $url . ': HTTP ' . $info['http_code'];
            //return $url . ': HTTP ' . $info['http_code'];
        } else {
            $this->curlResult[$url] = '';
            //return '';
        }
        return $this->curlResult[$url];
    }

    protected function hostnameTest($s)
    {

    }

    protected function historyTest($url)
    {
        $tmp = json_decode(file_get_contents('http://127.0.0.1:11626/info'), 1);
        $ledger = $tmp['info']['ledger']['num'] - 200;

        $url = preg_replace('/\/$/', '', $url);
        $url = $url . '/.well-known/stellar-history.json';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2); //timeout in seconds
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'stellar.toml checker https://stellar.sui.li/toml-check');

        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!($response = curl_exec($ch))) {
            return $url . ': ' . curl_error($ch);
        }
        $info = curl_getinfo($ch);
        if (!in_array($info['http_code'], [200, 301, 302])) {
            return $url . ': HTTP ' . $info['http_code'];
        }
        $d = json_decode($response, 1);

        if (empty($d)) {
            return $url . ': JSON parse error.';
        }

        if ($d['currentLedger'] < $ledger) {
            return $url . ": currentLedger=" . $d['currentLedger'] . " is not up to date (expected: > " . $ledger . ").";
        }
    }

    protected function stellarHistoryTest($urls)
    {
        $tmp = json_decode(file_get_contents('http://127.0.0.1:11626/info'), 1);
        $ledger = $tmp['info']['ledger']['num'] - 200;


        foreach ($urls as $url) {
            $url = preg_replace('/\/$/', '', $url);
            $url = $url . '/.well-known/stellar-history.json';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2); //timeout in seconds
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'stellar.toml checker https://stellar.sui.li/toml-check');

            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if (!($response = curl_exec($ch))) {
                return $url . ': ' . curl_error($ch);
            }
            $info = curl_getinfo($ch);
            if (!in_array($info['http_code'], [200, 301, 302])) {
                return $url . ': HTTP ' . $info['http_code'];
            }
            $d = json_decode($response, 1);

            if (empty($d)) {
                return $url . ': JSON parse error.';
            }

            if ($d['currentLedger'] < $ledger) {
                return $url . ": currentLedger=" . $d['currentLedger'] . " is not up to date (expected: > " . $ledger . ").";
            }
        }
    }

    protected function nodenamesArrayTest($a)
    {
        $errors = [];
        foreach ($a as $item) {
            $key = substr($item, 0, 56);
            $name = trim(substr($item, 57));
            if (empty($name)) {
                $errors[] = 'No name defined for ' . $item;
            } else {
                $this->nodeNames[$name] = $key;
            }
            if ($valid = $this->publicKeyTest($key)) {
                $errors[] = $valid;
            }
        }
        return $errors;
    }

    protected function intTest($s)
    {
        if (!is_int($s)) {
            return $s . ' is not an integer';
        }
    }


    protected function isValidatorTest($key, $ownValidator = false)
    {
        $sKey = $key;
        if (substr($key, 0, 1) == '$' && isset($this->nodeNames[substr($key, 1)])) {
            $key = $this->nodeNames[substr($key, 1)];
        }

        if (!in_array($key, $this->stellarbeat)) {
            return $sKey . ' not listed or inactive according to stellarbeat.io';
        }

        if ($ownValidator) {
            $acc = json_decode(file_get_contents('https://horizon.stellar.org/accounts/' . $key), 1);
            if (!isset($acc['home_domain'])) {
                return '(onchain) Account ' .  $key . ' home_domain not set.';
            } elseif ($acc['home_domain'] != $_REQUEST['home_domain']) {
                return '(onchain) Account ' . $key . ' has set home_domain = ' . htmlspecialchars($acc['home_domain'] . ' (expected: ' . htmlspecialchars($_REQUEST['home_domain']) . ').');
            }
        }
    }

    protected function publicKeyTest($key)
    {
        if (substr($key, 0, 1) == '$') {
            if (!isset($this->nodeNames[substr($key, 1)])) {
                return 'Not found in NODE_NAMES: ' . $key;
            }
        } else {
            try {
                $key = \ZuluCrypto\StellarSdk\Keypair::newFromPublicKey($key);
            } catch (Exception $e) {
                return 'Invalid PublicKey: ' . $key;
            }
        }
    }
}
