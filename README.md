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
declare (strict_types=1);

use xy_jx\Utils\Rmb;
use xy_jx\Utils\Captcha;
use xy_jx\Utils\Captchas;
class xy
{
   echo Rmb::rmb_capital(159622);
   
   // 初始化验证码类
    $Captcha = new Captcha;
    // 生成验证码和key
    $cap = $Captcha->create();
    // 验证是否正确
    var_dump($Captcha->check($cap['code'],$cap['key']) );// true
    exit();
   
   // 初始化验证码类   注意有两个验证码类，不要混淆调用
    $Captcha = new Captchas;
    $Captcha->set('length', 5);
    // 生成验证码
    $Captcha->build();
    // 获得验证码图片二进制数据
    //$img_content = $Captcha->getCode();
    // 输出验证码二进制数据
    //return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    //保存成图片
    //$Captcha->save('1111.png');
    //输出图片
    //$Captcha->output();
    // 获取base64图像
    $img_content = $Captcha->inline(10);
    //获取密钥（密钥没有存储到session或Cookie）可自己存储 防止用户重复使用
    $secretKey = $Captcha->secretKey();
    //获取图片内容 验证码
    $code = $Captcha->get('phrase');
    //判断验证码是否正确
    var_dump($Captcha->check($code, $secretKey));// true
    echo $img_content;//ata:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...
    exit();
   
}
```