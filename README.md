<h1 align="center">工具插件</h1>

[![Latest Stable Version](http://poser.pugx.org/xy_jx/utils/v)](https://packagist.org/packages/xy_jx/utils)
[![Total Downloads](http://poser.pugx.org/xy_jx/utils/downloads)](https://packagist.org/packages/xy_jx/utils)
[![Latest Unstable Version](http://poser.pugx.org/xy_jx/utils/v/unstable)](https://packagist.org/packages/xy_jx/utils)
[![License](http://poser.pugx.org/xy_jx/utils/license)](https://packagist.org/packages/xy_jx/utils)
[![PHP Version Require](http://poser.pugx.org/xy_jx/utils/require/php)](https://packagist.org/packages/xy_jx/utils)

<p  align="center">
1.生成二维码
2.xml与数组转换
3.生成范围经纬度
4.经纬度换算距离
5.人民币转大写
6.MurmurHash
7.GoogleAuth
8.对称加密和证书加密
9.访问限制
10.生成证书
11.验证码
12.Excel导出
13.jwt
</p>

### 第一步：composer安装

```
 composer require xy_jx/utils
```

### 第二步使用：

#### 获取随机字符串
```
     echo  rand_string();//KPV1
```
#### 获取UUID
```
      echo  UUID();//0b90f8b2-dca8-4ee8-86a1-f2a990605912
```
#### 数字人民币转汉字大写
```
      echo  rmb_capital(159622);//壹拾伍万玖仟陆佰贰拾贰圆
```
#### 验证码
```
use xy_jx\Utils\Captcha;
class xy
{      
        // 初始化验证码类
        $Captcha = new Captcha();
        // 生成验证码和key  （密钥没有存储到session或Cookie）可自己存储 防止用户重复使用
        $cap = $Captcha->create();
        //echo $cap['img'];//data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAA+CAMAAABZTaSoAAAAclBMVEXOvPAKZhygpcukoLrfprXhuaOenLe+n8ianayatMa...
        // 验证是否正确
        var_dump($Captcha->check($cap['code'], $cap['key']));// true
}
```
#### jwt
```
use xy_jx\Utils\Jwt;
class xy
{      
        //设置额外的密钥
        Jwt::set('iv', '@user@token@jwt*');
        $user  = [
            'id'        => 5,
            'tel'       => '188888888888',
            'name'      => 'xy',
            'email'     => 'xy@email.com',
            'sex'       => 2,
            'login_num' => 12,
        ];
        //获取token
        $token = Jwt::getToken($user);
        //通过token获取用户数据
        $user  = Jwt::getUser($token['token']);
        var_dump($user);
}
```
#### 加解密数据
```
use xy_jx\Utils\Encryption;
class xy
{      
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
}
```
#### 谷歌验证码GoogleAuthenticator
```
use xy_jx\Utils\GoogleAuthenticator;
class xy
{      
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
 
}
```
#### Excel导出
##### 额外安装phpspreadsheet
```
composer require phpoffice/phpspreadsheet
```
```
use xy_jx\Utils\Excel;
class xy
{      
        $userAll = [
            [
                'id'   => 1,
                'name' => '张三',
                'cd'   => date('Y-m-d'),
            ],
            [
                'id'   => 2,
                'name' => '李四',
                'cd'   => date('Y-m-d'),
            ],
            [
                'id'   => 3,
                'name' => '王五',
                'cd'   => date('Y-m-d'),
            ],
        ];
        
        Excel::header('用户导出', ['id' => 'ID', 'name' => '名字', 'cd' => '时间'])
            ->content($userAll)->save('Xlsx', true, '用户.Xlsx');
}
```
####  其他方法使用也如此（很简单可自行查看代码都有注释）
####  有什么建议和意见，可通过issue或邮箱反馈