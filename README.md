# xy

## 一个简单的示例

## 安装

第一步：

```
 composer require xy_jx/utils
```

第二步：

```
<?php
use xy_jx\Utils\Encryption;
use xy_jx\Utils\Jwt;
use xy_jx\Utils\Rmb;
use xy_jx\Utils\Captcha;
use xy_jx\Utils\GoogleAuthenticator;
class xy
{
        //获取随机字符串====================================================
        echo rand_string();//KPV1
        
        //获取UUID========================================================
        echo UUID();//0b90f8b2-dca8-4ee8-86a1-f2a990605912
        
        //数字人民币转汉字大写===========================================
        echo Rmb::rmbCapital(159622);//壹拾伍万玖仟陆佰贰拾贰圆
        
        //验证码=======================================================
        // 初始化验证码类
        $Captcha = new Captcha();
        // 生成验证码和key  （密钥没有存储到session或Cookie）可自己存储 防止用户重复使用
        $cap = $Captcha->create();
        //echo $cap['img'];//data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAA+CAMAAABZTaSoAAAAclBMVEXOvPAKZhygpcukoLrfprXhuaOenLe+n8ianayatMa...
        // 验证是否正确
        var_dump($Captcha->check($cap['code'], $cap['key']));// true
        
        //jwt=======================================================
        Jwt::set('iv', '@user@token@jwt*');
        $user  = [
            'id'        => 5,
            'tel'       => '188888888888',
            'name'      => 'xy',
            'email'     => 'xy@email.com',
            'sex'       => 2,
            'login_num' => 12,
        ];
        $token = Jwt::getToken();
        $user  = Jwt::getTokenData($token['token']);
        var_dump($user);
        
        //加解密数据====================================================
        $data = [
            'id'        => 5,
            'tel'       => '188888888888',
            'name'      => 'xy',
            'email'     => 'xy@email.com',
            'sex'       => 2,
            'login_num' => 12,
        ];
        //加密数据
        $encrypt = Encryption::Encrypt($data);
        //解密数据
        var_dump(Encryption::Decrypt($encrypt));
        
        //谷歌验证码GoogleAuthenticator==================================
        //创建一个密钥
        $secret = GoogleAuthenticator::createSecret();//WQI5IOGD6WSRHDIFNFHYJCHANUJZDMAG
        //通过密钥获取一个验证码
        $code = GoogleAuthenticator::getCode($secret);//273079
        //通过密钥验证code
        var_dump(GoogleAuthenticator::verifyCode($secret, $code));// true
        //获取第3方绑定二维码（从google图表中获取图像的QR码URL）
        echo GoogleAuthenticator::getQRCodeGoogleUrl(
            $name = 'xy',
            $secret,
            $title = '绑定密钥'
        );//https://api.qrserver.com/v1/create-qr-code/?data=otpauth%3A%2F%2Ftotp%2Fxy%3Fsecret%3DG3HVLCM5OCO6GTLCVNTD35UFIO4L6GB3%26issuer%3D%25E7%25BB%2591%25E5%25AE%259A%25E5%25AF%2586%25E9%2592%25A5&size=200x200&ecc=M      
        
        //其他方法使用也如此（很简单可以查看源码都有注释）
}
```