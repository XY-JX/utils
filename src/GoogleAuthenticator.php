<?php
// +----------------------------------------------------------------------
// | DATE: 2021/8/9 19:43
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  谷歌二步验证
// +----------------------------------------------------------------------

namespace xy_jx\Utils;


class GoogleAuthenticator
{
    protected static $_codeLength = 6;


    /**
     * Create new secret. (创建新秘密)
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param  int  $secretLength
     *
     * @return string
     * @throws \Exception
     */
    public static function createSecret(int $secretLength = 32): string
    {
        $validChars = self::_getBase32LookupTable();

        // Valid secret lengths are 80 to 640 bits
        if ($secretLength < 16 || $secretLength > 128) {
            throw new \Exception('Bad secret length');
        }
        $secret = '';
        $rnd    = false;
        if (function_exists('random_bytes')) {
            $rnd = random_bytes($secretLength);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $rnd = openssl_random_pseudo_bytes($secretLength, $cryptoStrong);
            if ( ! $cryptoStrong) {
                $rnd = false;
            }
        } elseif (function_exists('mcrypt_create_iv')) {
            $rnd = mcrypt_create_iv($secretLength, MCRYPT_DEV_URANDOM);
        }
        if ($rnd !== false) {
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            throw new \Exception('No source of secure random');
        }

        return $secret;
    }

    /**
     * Calculate the code, with given secret and point in time.
     * 用给定的秘密和时间点计算验证码。
     *
     * @param  string  $secret
     * @param  int|null  $timeSlice
     *
     * @return string
     */
    public static function getCode(
        string $secret,
        int $timeSlice = null
    ): string {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = self::_base32Decode($secret);

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, self::$_codeLength);

        return str_pad($value % $modulo, self::$_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR-Code URL for image, from google charts.
     * 从google图表中获取图像的QR码URL
     *
     * @param  string  $name
     * @param  string  $secret
     * @param  string|null  $title
     * @param  array  $params
     *
     * @return string
     */
    public static function getQRCodeGoogleUrl(
        string $name,
        string $secret,
        string $title = null,
        array $params = array()
    ): string {
        $width  = ! empty($params['width']) && (int)$params['width'] > 0
            ? (int)$params['width'] : 200;
        $height = ! empty($params['height']) && (int)$params['height'] > 0
            ? (int)$params['height'] : 200;
        $level  = ! empty($params['level'])
        && array_search(
            $params['level'],
            array('L', 'M', 'Q', 'H')
        ) !== false ? $params['level'] : 'M';

        $urlencoded = urlencode('otpauth://totp/'.$name.'?secret='.$secret.'');
        if (isset($title)) {
            $urlencoded .= urlencode('&issuer='.urlencode($title));
        }

        return "https://api.qrserver.com/v1/create-qr-code/?data=$urlencoded&size=${width}x${height}&ecc=$level";
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now.
     * 检查验证码是否正确。这将接受从30秒前的$DISCENCE*30秒到现在的$DISCENCE*30秒的代码
     *
     * @param  string  $secret
     * @param  string  $code
     * @param  int  $discrepancy  This is the allowed time drift in 30 second units (8 means 4 minutes before or after) 1*30 秒验证区间
     * @param  int|null  $currentTimeSlice  time slice if we want use other that time()
     *
     * @return bool
     */
    public static function verifyCode(
        string $secret,
        string $code,
        int $discrepancy = 1,
        int $currentTimeSlice = null
    ): bool {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

//        if (strlen($code) != 6) {
//            return false;
//        }

        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if (self::timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the code length, should be >=6.
     * 设置验证码的长度，应大于等于6
     *
     * @param  int  $length
     *
     * @return PHPGangsta_GoogleAuthenticator
     */
    public static function setCodeLength(int $length)
    {
        self::$_codeLength = $length;

        return self::class;
    }

    /**
     * Helper class to decode base32.
     * 用于解码base32的助手类
     *
     * @param $secret
     *
     * @return bool|string
     */
    protected static function _base32Decode($secret)
    {
        if (empty($secret)) {
            return '';
        }

        $base32chars        = self::_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues    = array(6, 4, 3, 1, 0);
        if ( ! in_array($paddingCharCount, $allowedValues)) {
            return false;
        }
        for ($i = 0; $i < 4; ++$i) {
            if ($paddingCharCount == $allowedValues[$i]
                && substr($secret, -($allowedValues[$i])) != str_repeat(
                    $base32chars[32],
                    $allowedValues[$i]
                )
            ) {
                return false;
            }
        }
        $secret       = str_replace('=', '', $secret);
        $secret       = str_split($secret);
        $binaryString = '';
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if ( ! in_array($secret[$i], $base32chars)) {
                return false;
            }
            for ($j = 0; $j < 8; ++$j) {
                $x .= str_pad(
                    base_convert(
                        @$base32charsFlipped[@$secret[$i + $j]],
                        10,
                        2
                    ),
                    5,
                    '0',
                    STR_PAD_LEFT
                );
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); ++$z) {
                $binaryString .= (($y = chr(
                        base_convert($eightBits[$z], 2, 10)
                    ))
                    || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32.
     * 获取包含所有32个字符的数组，以便从base32解码/编码到base32
     *
     * @return array
     */
    protected static function _getBase32LookupTable(): array
    {
        return array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H', //  7
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P', // 15
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X', // 23
            'Y',
            'Z',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7', // 31
            '=',  // padding char
        );
    }

    /**
     * A timing safe equals comparison
     * 时间安全等于比较
     * more info here: http://blog.ircmaxell.com/2014/11/its-all-about-time.html.
     *
     * @param  string  $safeString  The internal (safe) value to be checked
     * @param  string  $userString  The user submitted (unsafe) value
     *
     * @return bool True if the two strings are identical
     */
    private static function timingSafeEquals(
        string $safeString,
        string $userString
    ): bool {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);

        if ($userLen != $safeLen) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        // They are only identical strings if $result is exactly 0...
        return $result === 0;
    }
}