#xy
##一个简单的示例
## 安装
第一步：
```shell
 composer require xy_jx/utils
```
第二步：

```
<?php
declare (strict_types=1);

use xy_jx/utils/Rmb;
class xy
{
   echo Rmb::rmb_capital(159622);
}
```