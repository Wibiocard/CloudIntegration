<?php

namespace App\Helpers;

use http;

class LinOtpRequestHelper
{
    const SERVER = 'https://otpsandbox.wibiocard.com';
    const USERNAME = 'linotp_adm';
    const PASSWORD = 'flPaPhaL';

    private $ch;

    public function __construct() {
        $this->ch = curl_init();
    }

    public function __destruct() {
        curl_close($this->ch);
    }

/*init()
        POST /admin/login
        This function is used to authenticate the admin user against the LinOTP server and retrive the session id to continue wirking.
        Parameters
            username – (required) the username
            password – (required) the password
*/
    public function init()
    {
        try
        {
            $answer = "";
            if (!function_exists("curl_init"))
                throw new Exception("cURL extension is not installed!");
                curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/login");
                curl_setopt($this->ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, "username=".self::USERNAME."&password=".self::PASSWORD);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
                curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($this->ch, CURLOPT_COOKIEJAR, 'cookie-name');
                curl_setopt($this->ch, CURLOPT_COOKIEFILE, '/var/www/ip4.x/file/tmp');

            $session = curl_exec($this->ch);
                if (curl_error($this->ch)) throw new Exception("cURL session not retrived!");
            $ObjectSession = json_decode($session);
            if($ObjectSession && $ObjectSession->{'result'}->{'status'} == true && $ObjectSession->{'result'}->{'value'} == true)
            {
                return $ObjectSession->{'result'}->{'status'};
            }
            else
                throw new Exception("cURL authentication failed!");
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
        return json_decode($answer);
    }

/*assign()
        POST /admin/assign
        assigns a token to a user, i.e. a binding between the token and the user is created.
        Parameters
        serial – (required) the serial number / identifier of the token
        user – (required) login user name
        pin – (optional) - the pin of the user pass
*/
    public function adminAssign($serial, $user, $pin='0000')
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/assign");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$serial."&user=".$user."&pin=".$pin);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*check_serial()
        GET /admin/check_serial
        This function checks, if a given serial will be unique. It returns True if the serial does not yet exist and new_serial as a new value for a serial, that does not exist, yet
        Parameters
            serial – (required) the serial number / identifier of the token
*/
    public function adminCheck($serial)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/check_serial?serial=".$serial);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*disable()
        POST /admin/disable
        disables a token given by serial or all tokens of a user
        Parameters
            serial – the token serial
            user – the user for whom all tokens will be disabled
*/
    public function adminDisableUser($user)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/disable");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "user=".$user);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminDisableToken($token)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/disable");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*enable()
        POST /admin/enable
        enables a token or all tokens of a user
        Parameters
            serial – (optional), the token serial number
            user – (optional), will enable all tokens of a user
*/
    public function adminEnableUser($user)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/enable");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "user=".$user);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminEnableToken($token)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/enable");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*getTokenOwner()
        GET /admin/getTokenOwner
        provide the userinfo of the token, which is specified as serial
        Parameters
            serial – the serial number of the token
*/
    public function adminGetTokenOwner()
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/getTokenOwner?serial=".$token);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*initToken()
        POST /admin/init
        initializes a token
        Parameters
            otpkey – (required) the hmac Key of the token
            genkey – (required) =1, if key should be generated. We either need otpkey or genkey
            keysize – (optional) either 20 or 32. Default is 20
            serial – (required) the serial number / identifier of the token
            description – (optional)
            pin – (optional) the pin of the user pass
            user – (optional) login user name
            realm – (optional) realm of the user
            type – (opt:ional) the type of the token
            tokenrealm – (optional) the realm a token should be put into
            otplen – (optional) length of the OTP value
            hashlib – (optional) used hashlib sha1 oder sha256
        NB: ocra2 & qrtoken are supported
*/
    public function adminInitOtpToken($otpkey, $serial, $pin='0000', $user='', $realm='', $type='HOTP', $tokenrealm='', $otplen=8, $hashlib='sha1')
    {
        $genkey = ($otpkey == "") ? 1 : 0;
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/init");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "otpkey=".$otpkey."&genkey=".$genkey."&serial=".$serial."&pin=".$pin."&user=".$user."&realm=".$realm."&type=".$type."&tokenrealm=".$tokenrealm."&otplen=".$otplen."&hashlib=".$hashlib);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*remove()
        POST /admin/remove
        deletes either a certain token given by serial or all tokens of a user
        Parameters
            serial –the serial number of the token
            user – (optional) , will delete all tokens of a user
*/
    public function adminRemoveUser($user)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/remove");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "user=".$user);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminRemoveToken($token)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/remove");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*reset()
        POST /admin/reset
        reset the FailCounter of a Token
        Parameters
            serial (user or) – to identify the tokens
*/
    public function adminResetUser($user)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/reset");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "user=".$user);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminResetToken($token)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/reset");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*resync()
    POST /admin/resync
    this function resync the token, if the counter on server side is out of sync with the physical token.
    Parameters
        serial – serial or user (required)
        user – s.o.
        otp1 – the next otp to be found
        otp2 – the next otp after the otp1
*/
    public function adminResyncUser($user, $otp1, $otp2)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/resync");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "user=".$user."&otp1=".$otp1."&otp2=".$otp2);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminResyncToken($token, $otp1, $otp2)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/resync");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token."&otp1=".$otp1."&otp2=".$otp2);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*set()
        POST /admin/set
        this function is used to set many different values of a token.
        Parameters
            serial – (optional)
            user – (optional)
            pin – (optional) - set the OTP PIN
            MaxFailCount – (optional) - set the maximum fail counter of a token
            SyncWindow – (optional) - set the synchronization window of the token
            OtpLen – (optional) - set the OTP Lenght of the token
            CounterWindow – (optional) - set the counter window (blank presses)
            hashlib – (optional) - set the hashing algo for HMAC tokens. This can be sha1, sha256, sha512
            timeWindow – (optional) - set the synchronize window for timebased tokens (in seconds)
            timeStep – (optional) - set the timestep for timebased tokens (usually 30 or 60 seconds)
            timeShift – (optional) - set the shift or timedrift of this token
            countAuthSuccessMax – (optional) - set the maximum allowed successful authentications
            countAuthSuccess – (optional) - set the counter of the successful authentications
            countAuth – (optional) - set the counter of authentications
            countAuthMax – (optional) - set the maximum allowed authentication tries
            validityPeriodStart – (optional) - set the start date of the validity period. The token can not be used before this date
            validityPeriodEnd – (optional) - set the end date of the validaity period. The token can not be used after this date
            phone – set the phone number for an SMS token
*/
    public function adminSetUser($user, $pin='0000', $MaxFailCount=10, $SyncWindow=0, $OtpLen=8, $CounterWindow=0, $hashlib='sha1', $timeWindow=0, $timeStep=30, $timeShift=10, $countAuthSuccessMax=0, $countAuthSuccess=0, $countAuth=0, $countAuthMax=0, $validityPeriodStart=0, $validityPeriodEnd=0, $phone='')
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/set");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "user=".$user."&pin=".$pin."&MaxFailCount=".$MaxFailCount."&SyncWindow=".$SyncWindow."&OtpLen=".$OtpLen."&CounterWindow=".$CounterWindow."&hashlib=".$hashlib."&timeWindow=".$timeWindow."&timeStep=".$timeStep."&timeShift=".$timeShift."&countAuthSuccessMax=".$countAuthSuccessMax."&countAuthSuccess=".$countAuthSuccess."&countAuth=".$countAuth."&countAuthMax=".$countAuthMax."&validityPeriodStart=".$validityPeriodStart."&validityPeriodEnd=".$validityPeriodEnd."&phone=".$phone);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminSetToken($token, $pin='0000', $MaxFailCount=10, $SyncWindow=0, $OtpLen=8, $CounterWindow=0, $hashlib='sha1', $timeWindow=0, $timeStep=30, $timeShift=10, $countAuthSuccessMax=0, $countAuthSuccess=0, $countAuth=0, $countAuthMax=0, $validityPeriodStart=0, $validityPeriodEnd=0, $phone='')
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/set");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token."&pin=".$pin."&MaxFailCount=".$MaxFailCount."&SyncWindow=".$SyncWindow."&OtpLen=".$OtpLen."&CounterWindow=".$CounterWindow."&hashlib=".$hashlib."&timeWindow=".$timeWindow."&timeStep=".$timeStep."&timeShift=".$timeShift."&countAuthSuccessMax=".$countAuthSuccessMax."&countAuthSuccess=".$countAuthSuccess."&countAuth=".$countAuth."&countAuthMax=".$countAuthMax."&validityPeriodStart=".$validityPeriodStart."&validityPeriodEnd=".$validityPeriodEnd."&phone=".$phone);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*show()
        GET /admin/show
        displays the list of the available tokens
        Parameters
            serial – (optional) only this serial will be displayed
            user –(optional) only the tokens of this user will be displayed. If the user does not exist, linotp will search tokens of users, who contain this substring.
            viewrealm – (optional) takes a realm, only the tokens in this realm will be displayed
            tokeninfo_format – (optional) if set to “json”, this will be supplied in embedded JSON otherwise, string format is returned with dates in format DD/MM/YYYY TODO
*/
    public function adminShowAll($viewrealm='', $tokeninfo_format='json')
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/show?viewrealm=".$viewrealm."&tokeninfo_format=".$tokeninfo_format);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminShowUser($user, $viewrealm='', $tokeninfo_format='json')
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/show?user=".$user."&viewrealm=".$viewrealm."&tokeninfo_format=".$tokeninfo_format);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }
    public function adminShowToken($token, $viewrealm='', $tokeninfo_format='json')
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/show?serial=".$token."&viewrealm=".$viewrealm."&tokeninfo_format=".$tokeninfo_format);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*tokenrealm()
        POST /admin/tokenrealm
        set the realms a token belongs to
        Parameters
            serial – (required) serial number of the token
            realms – (required) comma seperated list of realms
*/
    public function adminTokenRealm($token, $realms)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/tokenrealm");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token."&realms=".$realms);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*unassign()
    POST /admin/unassign
    unassigns a token from a user. i.e. the binding between the token and the user is removed
    Parameters
        serial – (required) - the serial number / identifier of the token
*/
    public function adminUnassignToken($token)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/unassign");
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, "serial=".$token);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }

/*userlist()
        GET /admin/userlist
        lists the user in a realm
        Parameters
            <searchexpr> – will be retrieved from the UserIdResolverClass
            realm – a realm, which is a collection of resolver configurations
            resConf – a destinct resolver configuration
            page – the number of page, which should be retrieved (optional)
            rp – the number of users per page (optional)
*/
    public function adminUserList($searchexpr='', $realm='', $resConf='', $page=1, $rp=100)
    {
        curl_setopt($this->ch, CURLOPT_URL, self::SERVER."/admin/userlist?searchexpr=".$searchexpr."&realm=".$realm."&resConf=".$resConf."&page=".$page."&rp=".$rp);
        curl_setopt($this->ch, CURLOPT_POST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $answer = curl_exec($this->ch);
        if (curl_error($this->ch)) return ("cURL request error!");
        return json_decode($answer);
    }


}
