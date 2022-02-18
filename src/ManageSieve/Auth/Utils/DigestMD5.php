<?php

namespace PhpSieveManager\ManageSieve\Auth\Utils;


/**
 * Client Side Digest-MD5 implementation
 *
 * RFC 2831 (http://www.ietf.org/rfc/rfc2831.txt)
 */
class DigestMD5
{
    /**
     * @var string
     */
    private $challenge;

    /**
     * @var string
     */
    private $digestUri;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $realm;

    /**
     * @var string|null
     */
    private $cnonce;

    /**
     * @param $challenge
     * @param $digestUri
     */
    public function __construct($challenge, $digestUri) {
        $this->challenge = $challenge;
        $this->digestUri = $digestUri;

        $this->params = [];
        $this->realm = "";
        $this->cnonce = null;

        $pexpression = '%(\w+)="(.+)"%';
        foreach (explode(',', base64_decode($challenge)) as $elt) {
            preg_match($pexpression, $elt, $matches);
            if (count($matches)) {
                continue;
            }
            $this->params[$matches[1]] = $matches[2];
        }
    }

    /**
     * @return string
     */
    private function makeCnonce() {
        $return = "";
        for ($i = 0; $i < 12; $i++) {
            $return .= rand(0, 0xff);
        }
        return base64_encode($return);
    }

    /**
     * @param $value string
     * @return string
     */
    private function digest($value) {
        return md5($value);
    }

    /**
     * @param $value string
     * @return string
     */
    private function hexdigest($value) {
        return bin2hex($this->digest($value));
    }

    /**
     * @param $username
     * @param $password
     * @param $check
     * @return string
     */
    private function makeResponse($username, $password, $check=false) {
        $a1 = $this->digest($username.':'.$this->realm.':'.$password).':'.
              $this->params["nonce"].':'.
              $this->cnonce;
        $a2 = "AUTHENTICATE:".$this->digestUri;

        if ($check) {
            $a2 = ':'.$this->digestUri;
        }

        $resp = $this->hexdigest($a1).":".$this->params["nonce"].":00000001:".$this->cnonce.":auth:".$this->hexdigest($a2);
        return $this->hexdigest($resp);
    }

    /**
     * @param $username
     * @param $password
     * @param $authz_id
     * @return string
     */
    public function response($username, $password, $authz_id=null) {
        if (array_key_exists('realm', $this->params)) {
            $this->realm = $this->params['realm'];
        }
        $this->cnonce = $this->makeCnonce();
        $responseValue = $this->makeResponse($username, $password);

        $dgres = 'username="'.$username.'",nonce="'.$this->params['nonce'].'",cnonce="'.$this->cnonce.'",nc=00000001,qop=auth,'.
                'digest-uri="'.$this->digestUri.'",response='.$responseValue;

        if ($authz_id) {
            $dgres .= ',authzid="'.$authz_id.'"';
        }

        return base64_encode($dgres);
    }
}