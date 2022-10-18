<?php
// +----------------------------------------------------------------------
// | DATE: 2022/10/11 13:45
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  构建新的captcha图像 , 如果传递了指纹参数，则使用该参数生成相同的图像
// +----------------------------------------------------------------------
namespace xy_jx\Utils;

use \Exception;

class Captcha
{
    /**
     * @var array
     */
    protected $fingerprint = [];

    /**
     * @var bool
     */
    protected $useFingerprint = false;

    /**
     * @var array
     */
    protected $textColor = [];

    /**
     * @var array
     */
    protected $lineColor = null;

    /**
     * @var array
     */
    protected $backgroundColor = null;

    /**
     * @var array
     */
    protected $backgroundImages = [];

    /**
     * @var resource
     */
    protected $contents = null;

    /**
     * @var string
     */
    protected $phrase = null;

    /**
     * @var bool
     */
    protected $distortion = true;

    /**
     * The maximum number of lines to draw in front of
     * the image. null - use default algorithm
     * 要在前面绘制的最大线数
     * 图像。null-使用默认算法
     */
    protected $maxFrontLines = null;

    /**
     * The maximum number of lines to draw behind
     * the image. null - use default algorithm
     * 要绘制的最大线数
     * 图像。null-使用默认算法
     */
    protected $maxBehindLines = null;

    /**
     * The maximum angle of char
     * 字符的最大角度
     */
    protected $maxAngle = 8;

    /**
     * The maximum offset of char
     * 字符的最大偏移量
     */
    protected $maxOffset = 5;

    /**
     * Is the interpolation enabled ?
     * 是否启用插值？
     * @var bool
     */
    protected $interpolation = true;

    /**
     * Ignore all effects
     * 忽略所有效果
     * @var bool
     */
    protected $ignoreAllEffects = false;

    /**
     * Allowed image types for the background images
     * 允许的背景图像类型
     * @var array
     */
    protected $allowedBackgroundImageTypes = ['image/png', 'image/jpeg', 'image/gif'];
    /**
     * 字符集
     * @var string
     */
    protected $charset = 'abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    /**
     * 图像内容长度
     * @var int
     */
    protected $length = 4;

    /**
     * The image contents
     * 图像内容
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Enable/Disables the interpolation
     * 启用/禁用插值
     * @param $interpolate bool  True表示启用，false表示禁用
     *
     * @return Captcha
     */
    public function setInterpolation($interpolate = true)
    {
        $this->interpolation = $interpolate;

        return $this;
    }

    /**
     * Temporary dir, for OCR check
     * 临时目录，用于OCR检查
     */
    public $tempDir = 'temp/';

    /**
     * 构造方法
     * @param $phrase string|int|null 指定内容长度或内容
     * @param $charset string|null 字符集
     */
    public function __construct($phrase = null, $charset = null)
    {
        $this->phrase = is_string($phrase) ? $phrase : $this->generate($phrase, $charset);
    }

    /**
     * 生成短语
     * @param string|null $length
     * @param $charset
     * @return string
     */
    protected function generate(string $length = null, $charset = null): string
    {
        if ($length !== null) {
            $this->length = $length;
        }
        if ($charset !== null) {
            $this->charset = $charset;
        }

        $code = '';
        $chars = str_split($this->charset);

        for ($i = 0; $i < $this->length; $i++) {
            $code .= $chars[array_rand($chars)];
        }

        return $code;
    }

    /**
     * Setting the phrase
     * 设置短语
     */
    public function setPhrase($phrase)
    {
        $this->phrase = (string)$phrase;
    }

    /**
     * Enables/disable distortion
     * 启用/禁用失真
     */
    public function setDistortion($distortion)
    {
        $this->distortion = (bool)$distortion;

        return $this;
    }

    public function setMaxBehindLines($maxBehindLines)
    {
        $this->maxBehindLines = $maxBehindLines;

        return $this;
    }

    public function setMaxFrontLines($maxFrontLines)
    {
        $this->maxFrontLines = $maxFrontLines;

        return $this;
    }

    public function setMaxAngle($maxAngle)
    {
        $this->maxAngle = $maxAngle;

        return $this;
    }

    public function setMaxOffset($maxOffset)
    {
        $this->maxOffset = $maxOffset;

        return $this;
    }

    /**
     * Gets the captcha phrase
     * 获取字幕短语
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    public function toPhrase($phrase)
    {
        return strtr(strtolower($phrase), '01', 'ol');
    }

    /**
     * Returns true if the given phrase is good
     * 判断给定短语正确，则返回true
     */
    public function testPhrase($phrase)
    {
        return $this->toPhrase($phrase) == $this->toPhrase($this->getPhrase());
    }

    /**
     * Instantiation
     * 实例化
     */
    public static function create($phrase = null)
    {
        return new self($phrase);
    }

    /**
     * 设置要使用的文本颜色
     */
    public function setTextColor($r, $g, $b)
    {
        $this->textColor = [$r, $g, $b];

        return $this;
    }

    /**
     * 设置要使用的背景色
     */
    public function setBackgroundColor($r, $g, $b)
    {
        $this->backgroundColor = [$r, $g, $b];

        return $this;
    }

    /**
     * 设置线条颜色
     * @param $r
     * @param $g
     * @param $b
     * @return $this
     */
    public function setLineColor($r, $g, $b)
    {
        $this->lineColor = [$r, $g, $b];

        return $this;
    }

    /**
     * Sets the ignoreAllEffects value
     * 设置忽略所有效果值
     *
     * @param bool $ignoreAllEffects
     * @return Captcha
     */
    public function setIgnoreAllEffects($ignoreAllEffects)
    {
        $this->ignoreAllEffects = $ignoreAllEffects;

        return $this;
    }

    /**
     * Sets the list of background images to use (one image is randomly selected)
     * 设置要使用的背景图像列表（随机选择一个图像）
     */
    public function setBackgroundImages(array $backgroundImages)
    {
        $this->backgroundImages = $backgroundImages;

        return $this;
    }

    /**
     * Draw lines over the image
     * 在图像上绘制线条
     */
    protected function drawLine($image, $width, $height, $tcol = null)
    {
        if ($this->lineColor === null) {
            $red = $this->rand(100, 255);
            $green = $this->rand(100, 255);
            $blue = $this->rand(100, 255);
        } else {
            $red = $this->lineColor[0];
            $green = $this->lineColor[1];
            $blue = $this->lineColor[2];
        }

        if ($tcol === null) {
            $tcol = imagecolorallocate($image, $red, $green, $blue);
        }

        if ($this->rand(0, 1)) { // 水平的
            $Xa = $this->rand(0, $width / 2);
            $Ya = $this->rand(0, $height);
            $Xb = $this->rand($width / 2, $width);
            $Yb = $this->rand(0, $height);
        } else { // 竖的
            $Xa = $this->rand(0, $width);
            $Ya = $this->rand(0, $height / 2);
            $Xb = $this->rand(0, $width);
            $Yb = $this->rand($height / 2, $height);
        }
        imagesetthickness($image, $this->rand(1, 3));
        imageline($image, $Xa, $Ya, $Xb, $Yb, $tcol);
    }

    /**
     * Apply some post effects
     * 应用一些后期效果
     */
    protected function postEffect($image)
    {
        if (!function_exists('imagefilter')) {
            return;
        }

        if ($this->backgroundColor != null || $this->textColor != null) {
            return;
        }

        // Negate ?    取消
        if ($this->rand(0, 1) == 0) {
            imagefilter($image, IMG_FILTER_NEGATE);
        }

        // Edge ?   边
        if ($this->rand(0, 10) == 0) {
            imagefilter($image, IMG_FILTER_EDGEDETECT);
        }

        // Contrast  明显的差异
        imagefilter($image, IMG_FILTER_CONTRAST, $this->rand(-50, 30));

        // Colorize  着色
        if ($this->rand(0, 5) == 0) {
            imagefilter($image, IMG_FILTER_COLORIZE, $this->rand(-80, 50), $this->rand(-80, 50), $this->rand(-80, 50));
        }
    }

    /**
     * Writes the phrase on the image
     * 在图像上写入短语
     */
    protected function writePhrase($image, $phrase, $font, $width, $height)
    {
        $length = mb_strlen($phrase);
        if ($length === 0) {
            return \imagecolorallocate($image, 0, 0, 0);
        }

        // 获取文本大小和开始位置
        $size = intval($width / $length) - $this->rand(0, 3) - 1;
        $box = \imagettfbbox($size, 0, $font, $phrase);
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = intval(($width - $textWidth) / 2);
        $y = intval(($height - $textHeight) / 2) + $size;

        if (!$this->textColor) {
            $textColor = [$this->rand(0, 150), $this->rand(0, 150), $this->rand(0, 150)];
        } else {
            $textColor = $this->textColor;
        }
        $col = \imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

        // 用随机角度逐个书写字母
        for ($i = 0; $i < $length; $i++) {
            $symbol = mb_substr($phrase, $i, 1);
            $box = \imagettfbbox($size, 0, $font, $symbol);
            $w = $box[2] - $box[0];
            $angle = $this->rand(-$this->maxAngle, $this->maxAngle);
            $offset = $this->rand(-$this->maxOffset, $this->maxOffset);
            \imagettftext($image, $size, $angle, $x, $y + $offset, $col, $font, $symbol);
            $x += $w;
        }

        return $col;
    }

    /**
     * Try to read the code against an OCR
     * 尝试根据OCR读取代码
     */
    public function isOCRReadable()
    {
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0755, true);
        }

        $tempj = $this->tempDir . uniqid('captcha', true) . '.jpg';
        $tempp = $this->tempDir . uniqid('captcha', true) . '.pgm';

        $this->save($tempj);
        shell_exec("convert $tempj $tempp");
        $value = trim(strtolower(shell_exec("ocrad $tempp")));

        @unlink($tempj);
        @unlink($tempp);

        return $this->testPhrase($value);
    }

    /**
     * Builds while the code is readable against an OCR
     * 根据OCR读取代码时生成
     */
    public function buildAgainstOCR($width = 150, $height = 40, $font = null, $fingerprint = null)
    {
        do {
            $this->build($width, $height, $font, $fingerprint);
        } while ($this->isOCRReadable());
    }

    /**
     * Generate the image
     * 生成图像
     */
    public function build($width = 150, $height = 40, $font = null, $fingerprint = null)
    {
        if (null !== $fingerprint) {
            $this->fingerprint = $fingerprint;
            $this->useFingerprint = true;
        } else {
            $this->fingerprint = [];
            $this->useFingerprint = false;
        }

        if ($font === null) {
            $font = $this->getFontPath(__DIR__ . '/../Font/' . $this->rand(1, 6) . '.ttf');
        }

        if (empty($this->backgroundImages)) {
            // 如果未设置背景图像列表，请使用颜色填充作为背景
            $image = imagecreatetruecolor($width, $height);
            if ($this->backgroundColor == null) {
                $bg = imagecolorallocate($image, $this->rand(180, 255), $this->rand(180, 255), $this->rand(180, 255));
            } else {
                $color = $this->backgroundColor;
                $bg = imagecolorallocate($image, $color[0], $color[1], $color[2]);
            }
            $this->background = $bg;
            imagefill($image, 0, 0, $bg);
        } else {
            // 使用随机背景图像
            $randomBackgroundImage = $this->backgroundImages[rand(0, count($this->backgroundImages) - 1)];

            $imageType = $this->validateBackgroundImage($randomBackgroundImage);

            $image = $this->createBackgroundImageFromType($randomBackgroundImage, $imageType);
        }

        // 应用效果
        if (!$this->ignoreAllEffects) {
            $square = $width * $height;
            $effects = $this->rand($square / 3000, $square / 2000);

            // 设置要在文本前面绘制的最大行数
            if ($this->maxBehindLines != null && $this->maxBehindLines > 0) {
                $effects = min($this->maxBehindLines, $effects);
            }

            if ($this->maxBehindLines !== 0) {
                for ($e = 0; $e < $effects; $e++) {
                    $this->drawLine($image, $width, $height);
                }
            }
        }

        // 写入验证码文本
        $color = $this->writePhrase($image, $this->phrase, $font, $width, $height);

        // 应用效果
        if (!$this->ignoreAllEffects) {
            $square = $width * $height;
            $effects = $this->rand($square / 3000, $square / 2000);

            // 设置要在文本前面绘制的最大行数
            if ($this->maxFrontLines != null && $this->maxFrontLines > 0) {
                $effects = min($this->maxFrontLines, $effects);
            }

            if ($this->maxFrontLines !== 0) {
                for ($e = 0; $e < $effects; $e++) {
                    $this->drawLine($image, $width, $height, $color);
                }
            }
        }

        // 绘干扰线
        if ($this->distortion && !$this->ignoreAllEffects) {
            $image = $this->distort($image, $width, $height, $bg);
        }

        // 后期效果
        if (!$this->ignoreAllEffects) {
            $this->postEffect($image);
        }

        $this->contents = $image;

        return $this;
    }

    /**
     * @param $font
     * @return string
     */
    protected function getFontPath($font)
    {
        static $fontPathMap = [];
        if (!\class_exists(\Phar::class, false) || !\Phar::running()) {
            return $font;
        }

        $tmpPath = sys_get_temp_dir() ?: '/tmp';
        $filePath = "$tmpPath/" . basename($font);
        clearstatcache();
        if (!isset($fontPathMap[$font]) || !is_file($filePath)) {
            file_put_contents($filePath, file_get_contents($font));
            $fontPathMap[$font] = $filePath;
        }
        return $fontPathMap[$font];
    }

    /**
     * Distorts the image
     * 绘干扰线
     */
    public function distort($image, $width, $height, $bg)
    {
        $contents = imagecreatetruecolor($width, $height);
        $X = $this->rand(0, $width);
        $Y = $this->rand(0, $height);
        $phase = $this->rand(0, 10);
        $scale = 1.1 + $this->rand(0, 10000) / 30000;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $Vx = $x - $X;
                $Vy = $y - $Y;
                $Vn = sqrt($Vx * $Vx + $Vy * $Vy);

                if ($Vn != 0) {
                    $Vn2 = $Vn + 4 * sin($Vn / 30);
                    $nX = $X + ($Vx * $Vn2 / $Vn);
                    $nY = $Y + ($Vy * $Vn2 / $Vn);
                } else {
                    $nX = $X;
                    $nY = $Y;
                }
                $nY = $nY + $scale * sin($phase + $nX * 0.2);

                if ($this->interpolation) {
                    $p = $this->interpolate(
                        $nX - floor($nX),
                        $nY - floor($nY),
                        $this->getCol($image, floor($nX), floor($nY), $bg),
                        $this->getCol($image, ceil($nX), floor($nY), $bg),
                        $this->getCol($image, floor($nX), ceil($nY), $bg),
                        $this->getCol($image, ceil($nX), ceil($nY), $bg)
                    );
                } else {
                    $p = $this->getCol($image, round($nX), round($nY), $bg);
                }

                if ($p == 0) {
                    $p = $bg;
                }

                imagesetpixel($contents, $x, $y, $p);
            }
        }

        return $contents;
    }

    /**
     * Saves the Captcha to a jpeg file
     * 将Captcha保存到jpeg文件
     */
    public function save($filename, $quality = 90)
    {
        imagejpeg($this->contents, $filename, $quality);
    }

    /**
     * Gets the image GD
     * 获取图像GD
     */
    public function getGd()
    {
        return $this->contents;
    }

    /**
     * Gets the image contents
     * 获得验证码图片二进制数据
     */
    public function get($quality = 90)
    {
        ob_start();
        $this->output($quality);

        return ob_get_clean();
    }

    /**
     * Gets the HTML inline base64
     * 获取base64图像
     */
    public function inline($quality = 90)
    {
        return 'data:image/jpeg;base64,' . base64_encode($this->get($quality));
    }

    /**
     * 获取密钥
     * @return false|string|null
     */
    public function secretKey()
    {
        return password_hash($this->toPhrase($this->getPhrase()), PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * 验证验证码是否正确
     * @param string $code 户验证码
     * @param string $key 密钥
     * @return bool 用户验证码是否正确
     */
    public function check(string $code, string $key): bool
    {
        return password_verify($this->toPhrase($code), $key);
    }

    /**
     * Outputs the image
     * 输出图像
     */
    public function output($quality = 90)
    {
        imagejpeg($this->contents, null, $quality);
    }

    /**
     * @return array
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * Returns a random number or the next number in the fingerprint
     * 返回一个随机数或指纹
     */
    protected function rand($min, $max)
    {
        if (!is_array($this->fingerprint)) {
            $this->fingerprint = [];
        }

        if ($this->useFingerprint) {
            $value = current($this->fingerprint);
            next($this->fingerprint);
        } else {
            $value = mt_rand(intval($min), intval($max));
            $this->fingerprint[] = $value;
        }

        return $value;
    }

    /**
     * @param $x
     * @param $y
     * @param $nw
     * @param $ne
     * @param $sw
     * @param $se
     *
     * @return int
     */
    protected function interpolate($x, $y, $nw, $ne, $sw, $se)
    {
        list($r0, $g0, $b0) = $this->getRGB($nw);
        list($r1, $g1, $b1) = $this->getRGB($ne);
        list($r2, $g2, $b2) = $this->getRGB($sw);
        list($r3, $g3, $b3) = $this->getRGB($se);

        $cx = 1.0 - $x;
        $cy = 1.0 - $y;

        $m0 = $cx * $r0 + $x * $r1;
        $m1 = $cx * $r2 + $x * $r3;
        $r = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $g0 + $x * $g1;
        $m1 = $cx * $g2 + $x * $g3;
        $g = (int)($cy * $m0 + $y * $m1);

        $m0 = $cx * $b0 + $x * $b1;
        $m1 = $cx * $b2 + $x * $b3;
        $b = (int)($cy * $m0 + $y * $m1);

        return ($r << 16) | ($g << 8) | $b;
    }

    /**
     * @param $image
     * @param $x
     * @param $y
     *
     * @return int
     */
    protected function getCol($image, $x, $y, $background)
    {
        $L = imagesx($image);
        $H = imagesy($image);
        if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
            return $background;
        }

        return imagecolorat($image, $x, $y);
    }

    /**
     * @param $col
     *
     * @return array
     */
    protected function getRGB($col)
    {
        return [
            (int)($col >> 16) & 0xff,
            (int)($col >> 8) & 0xff,
            (int)($col) & 0xff,
        ];
    }

    /**
     * Validate the background image path. Return the image type if valid
     * 验证背景图像路径。如果有效，则返回图像类型
     * @param string $backgroundImage
     * @return string
     * @throws Exception
     */
    protected function validateBackgroundImage($backgroundImage)
    {
        // check if file exists 检查文件是否存在
        if (!file_exists($backgroundImage)) {
            $backgroundImageExploded = explode('/', $backgroundImage);
            $imageFileName = count($backgroundImageExploded) > 1 ? $backgroundImageExploded[count($backgroundImageExploded) - 1] : $backgroundImage;

            throw new Exception('Invalid background image: ' . $imageFileName);
        }

        // check image type 检查图像类型
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $imageType = finfo_file($finfo, $backgroundImage);
        finfo_close($finfo);

        if (!in_array($imageType, $this->allowedBackgroundImageTypes)) {
            throw new Exception('Invalid background image type! Allowed types are: ' . join(', ', $this->allowedBackgroundImageTypes));
        }

        return $imageType;
    }

    /**
     * Create background image from type
     * 根据类型创建背景图像
     * @param string $backgroundImage
     * @param string $imageType
     * @return resource
     * @throws Exception
     */
    protected function createBackgroundImageFromType($backgroundImage, $imageType)
    {
        switch ($imageType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($backgroundImage);
                break;
            case 'image/png':
                $image = imagecreatefrompng($backgroundImage);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($backgroundImage);
                break;

            default:
                throw new Exception('Not supported file type for background image!');
                break;
        }

        return $image;
    }
}
