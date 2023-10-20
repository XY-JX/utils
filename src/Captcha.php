<?php
// +----------------------------------------------------------------------
// | DATE: 2022/10/12 13:45
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  构建新的captcha图像 , 如果传递了指纹参数，则使用该参数生成相同的图像
// +----------------------------------------------------------------------
namespace xy_jx\Utils;

class Captcha
{
    private $im = null; // 验证码图片实例
    private $color = null; // 验证码字体颜色
    // 验证码字符集合
    protected $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
    // 验证码字体大小(px)
    protected $fontSize = 25;
    // 是否画混淆曲线
    protected $useCurve = true;
    // 是否添加杂点
    protected $useNoise = true;
    // 验证码图片高度
    protected $imageH = 0;
    // 验证码图片宽度
    protected $imageW = 0;
    // 验证码位数
    protected $length = 4;
    // 验证码字体，不设置随机获取
    protected $fontttf = '';
    // 背景颜色
    protected $bg = [180, 255];
    //算术验证码
    protected $math = false;
    //加密等级
    protected $encryptionLevel = 5;
    //背景图片
    protected $backgroundImages = [];

    /**
     * 架构方法 设置参数
     * 可修改 protected 参数
     *
     * @param array $config ['length'=>5]
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }

    /**
     * 设置参数
     *
     * @param $name
     * @param $val
     *
     * @return Captcha
     */
    public function set($name, $val): Captcha
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $val;
        }

        return $this;
    }

    /**
     * 获取配置参数
     *
     * @param $name
     *
     * @return string
     */
    public function get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        return '';
    }

    /**
     * 创建验证码
     *
     * @param string $value
     *
     * @return array
     */
    protected function generate(string $value = ''): array
    {
        if ($value) {
            $code = mb_strtolower($value, 'UTF-8');
        } elseif ($this->math) {
            [$value, $code] = $this->mathOperation();
        } else {
            $value = rand_string($this->length, $this->codeSet);
            $code = mb_strtolower($value, 'UTF-8');
        }

        return [
            'value' => $value,
            'key' => password_hash(
                $code,
                PASSWORD_BCRYPT,
                ['cost' => $this->encryptionLevel]
            ),
            'code' => $code,
        ];
    }

    /**
     * 验证验证码是否正确
     *
     * @param string $code 户验证码
     * @param string $key 密钥
     *
     * @return bool 用户验证码是否正确
     */
    public function check(string $code, string $key): bool
    {
        $code = mb_strtolower($code, 'UTF-8');

        return password_verify($code, $key);
    }

    /**
     * 生成验证码
     *
     * @param $code 默认值
     *
     * @return array
     */
    public function create($code = ''): array
    {
        $generator = $this->generate($code);

        // 图片宽(px)
        $this->imageW || $this->imageW = intval($this->length * $this->fontSize * 1.5 + $this->length * $this->fontSize / 2);
        // 图片高(px)
        $this->imageH || $this->imageH = intval($this->fontSize * 2.5);
        // 建立一幅 $this->imageW x $this->imageH 的图像
        $this->im = imagecreate($this->imageW, $this->imageH);

        // 设置背景
        if ($this->backgroundImages) {
            $this->background();
        } else {
            imagecolorallocate(
                $this->im,
                mt_rand($this->bg[0], $this->bg[1]),
                mt_rand($this->bg[0], $this->bg[1]),
                mt_rand($this->bg[0], $this->bg[1])
            );
        }

        // 验证码字体随机颜色
        $this->color = imagecolorallocate(
            $this->im,
            mt_rand(1, 150),
            mt_rand(1, 150),
            mt_rand(1, 150)
        );

        // 验证码使用随机字体
        if ($this->math) {
            $fontttf = $this->fontttf ?: __DIR__ . '/../Font/' . mt_rand(1, 5) . '.ttf';
        } else {
            $fontttf = $this->fontttf ?: __DIR__ . '/../Font/' . mt_rand(1, 6) . '.ttf';
        }

        if ($this->useNoise) {
            // 绘杂点
            $this->writeNoise();
        }
        if ($this->useCurve) {
            // 绘干扰线
            $this->writeCurve();
        }

        // 绘验证码
        $text = str_split($generator['value']); // 验证码

        foreach ($text as $index => $char) {
            $x = intval($this->fontSize * ($index + 1) * ($this->math ? 1 : 1.5));
            $y = $this->fontSize + mt_rand(10, 20);
            $angle = $this->math ? 0 : mt_rand(-40, 40);
            imagettftext(
                $this->im,
                $this->fontSize,
                $angle,
                $x,
                $y,
                $this->color,
                $fontttf,
                $char
            );
        }

        ob_start();
        // 输出图像
        imagepng($this->im);
        $content = ob_get_clean();
        imagedestroy($this->im);

        return [
            'key' => $generator['key'],
            'code' => $generator['code'],
            'img' => 'data:image/png;base64,' . base64_encode($content),
        ];
    }

    /**
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数)
     *
     *      高中的数学公式咋都忘了涅，写出来
     *        正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    protected function writeCurve(): void
    {
        $px = $py = 0;

        // 曲线前部分
        $A = mt_rand(1, intval($this->imageH / 2)); // 振幅
        $b = mt_rand(-intval($this->imageH / 4), intval($this->imageH / 4)); // Y轴方向偏移量
        $f = mt_rand(-intval($this->imageH / 4), intval($this->imageH / 4)); // X轴方向偏移量
        $T = mt_rand($this->imageH, $this->imageW * 2); // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0; // 曲线横坐标起始位置
        $px2 = mt_rand(intval($this->imageW / 2), intval($this->imageW * 0.8)); // 曲线横坐标结束位置

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = intval($A * sin($w * $px + $f) + $b + $this->imageH / 2); // y = Asin(ωx+φ) + b
                $i = (int)($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel(
                        $this->im,
                        $px + $i,
                        $py + $i,
                        $this->color
                    ); // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    $i--;
                }
            }
        }

        // 曲线后部分
        $A = mt_rand(1, intval($this->imageH / 2)); // 振幅
        $f = mt_rand(-intval($this->imageH / 4), intval($this->imageH / 4)); // X轴方向偏移量
        $T = mt_rand($this->imageH, intval($this->imageW * 2)); // 周期
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $this->imageH / 2;
        $px1 = $px2;
        $px2 = $this->imageW;

        for ($px = $px1; $px <= $px2; $px = $px + 1) {
            if (0 != $w) {
                $py = intval($A * sin($w * $px + $f) + $b + $this->imageH / 2); // y = Asin(ωx+φ) + b
                $i = (int)($this->fontSize / 5);
                while ($i > 0) {
                    imagesetpixel($this->im, $px + $i, $py + $i, $this->color);
                    $i--;
                }
            }
        }
    }

    /**
     * 数学运算
     * @return string[]
     */
    protected function mathOperation()
    {
        $this->length = 5;
        $x = mt_rand(0, 99);
        $y = mt_rand(0, 99);
        $value = $code = '';
        switch (mt_rand(1, 2)) {
            case 1;
                $value = "{$x} + {$y} = ";
                $code = $x + $y;
                break;
            case 2;
                $value = ($x + $y) . " - {$y} = ";
                $code = $x;
                break;
        }
        $code .= '';
        return [$value, $code];
    }

    /**
     * 画杂点
     * 往图片上写不同颜色的字母或数字
     */
    protected function writeNoise(): void
    {
        for ($i = 0; $i < 10; $i++) {
            //杂点颜色
            $noiseColor = imagecolorallocate(
                $this->im,
                mt_rand(150, 225),
                mt_rand(150, 225),
                mt_rand(150, 225)
            );
            for ($j = 0; $j < 5; $j++) {
                // 绘杂点
                imagestring(
                    $this->im,
                    10,
                    mt_rand(-5, $this->imageW),
                    mt_rand(-5, $this->imageH),
                    rand_string(1),
                    $noiseColor
                );
            }
        }
    }

    /**
     * 绘制背景图片
     * 注：如果验证码输出图片比较大，将占用比较多的系统资源
     */
    protected function background(): void
    {
        $gb = $this->backgroundImages[array_rand($this->backgroundImages)];

        list($width, $height) = @getimagesize($gb);
        // Resample
        $bgImage = @imagecreatefromjpeg($gb);
        @imagecopyresampled(
            $this->im,
            $bgImage,
            0,
            0,
            0,
            0,
            $this->imageW,
            $this->imageH,
            $width,
            $height
        );
        @imagedestroy($bgImage);
    }

}