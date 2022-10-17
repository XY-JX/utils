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

use xy_jx/Utils/Rmb;
use xy_jx\Utils\Captcha;
class xy
{
   echo Rmb::rmb_capital(159622);
   
    $cap = new Captcha(['length'=>5]);//自己保存key，可存入redis 防止反复使用
    print_r($a=$cap->create());
    echo $a['code'];
    var_dump($cap->check($a['code'],$a['key']));
   
}
```