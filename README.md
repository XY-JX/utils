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
class xy
{
   echo Rmb::rmb_capital(159622);
   
    // 初始化验证码类 new Captcha(3,'qwertyuiop123456');
    $Captcha = new Captcha;
    // 生成验证码
    $Captcha->build();
    // 获得验证码图片二进制数据
    //$img_content = $Captcha->get();
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
    //获取图片内容
    $code = $Captcha->getPhrase();
    //判断验证码是否正确
    var_dump($Captcha->check($code, $secretKey));// true
    echo $img_content;//ata:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...
    exit();
   
}
```