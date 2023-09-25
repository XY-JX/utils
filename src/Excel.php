<?php
// +----------------------------------------------------------------------
// | DATE: 2022/2/9 19:43
// +----------------------------------------------------------------------
// | Author: xy <zhangschooi@qq.com>
// +----------------------------------------------------------------------
// | Notes:  Excel
// +----------------------------------------------------------------------
namespace xy_jx\Utils;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Class Excel 出于某些原因请手动安装扩展
 * export 相关需要安装额外扩展  composer require phpoffice/phpspreadsheet
 */
class Excel
{
    private static $cellKey
        = [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ',
            'BA',
            'BB',
            'BC',
            'BD',
            'BE',
            'BF',
            'BG',
            'BH',
            'BI',
            'BJ',
            'BK',
            'BL',
            'BM',
            'BN',
            'BO',
            'BP',
            'BQ',
            'BR',
            'BS',
            'BT',
            'BU',
            'BV',
            'BW',
            'BX',
            'BY',
            'BZ',
        ];
    private static $width_row = [];//区间
    private static $spreadsheet;
    private static $worksheet;
    private static $title;
    private static $Prow = 1;
    private static $list_key = [];
    private static $style
        = [
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],
            'borders'   => [
                'outline' => [
                    'borderStyle' => 'thin',
                    'color'       => ['argb' => '000000'],
                ],
            ],
            'font'      => [
                'name' => '黑体',
                'bold' => true,
                'size' => 14,
            ],
        ];

    /**
     * 初始化
     *
     * @throws \Exception
     */
    private static function initialize()
    {
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            self::$spreadsheet = new Spreadsheet();
            self::$worksheet   = self:: $spreadsheet->getActiveSheet();
            self::$worksheet->getDefaultColumnDimension()->setWidth(12);//设置默认宽度
            //self::$worksheet->getDefaultRowDimension()->setRowHeight(16);//设置默认行高
        } else {
            throw new \Exception(
                'not installed : PhpOffice\PhpSpreadsheet , solution : composer require phpoffice/phpspreadsheet'
            );
        }
    }

    /**
     * 头部
     *
     * @param  string  $title  标题
     * @param  array  $header  栏目 ['id' => '编号', 'name' => '名字', 'time' => '时间']
     * @param  string  $subtitle  副标题
     *
     * @return Excel|string
     */
    public static function header(
        string $title = '标题',
        array $header = [],
        string $subtitle = ''
    ) {
        self::initialize();
        $length      = count($header);
        self::$title = date('Y-m-d').' '.$title;
        if ($length > 0) {
            self::$width_row = array_slice(self::$cellKey, 0, $length);
        } else {
            return '表格数量字段异常';
        }

        self::$worksheet->setTitle(self::$title); //设置导出文件名
        self::$worksheet->getRowDimension(self::$Prow)->setRowHeight(
            22
        );//设置第一行行高
        $titleMerge = reset(self::$width_row).self::$Prow.':'.end(
                self::$width_row
            ).self::$Prow;
        self::$worksheet->mergeCells($titleMerge);
        self::$worksheet->getStyle($titleMerge)->applyFromArray(self::$style);
        self::$worksheet->setCellValueByColumnAndRow(
            1,
            self::$Prow,
            self::$title
        );
        self::$style['font']['size'] = 12;

        if ($subtitle) {   //设置副标题
            self::$Prow++;
            $Subtitle = reset(self::$width_row).self::$Prow.':'.end(
                    self::$width_row
                ).self::$Prow;
            self::$worksheet->mergeCells($Subtitle);
            self::$worksheet->getStyle($Subtitle)->applyFromArray(self::$style);
            self::$worksheet->setCellValueByColumnAndRow(
                1,
                self::$Prow,
                $subtitle
            );
        }
        //设置列表标题
        self::bander($header);

        return new self;
    }

    /**
     * 设置导航
     */
    private static function bander($data)
    {
        self::$Prow++;
        $index = 0;
        foreach ($data as $k => $item) {
            self::$list_key[] = $k;
            $index++;
            self:: $worksheet->setCellValueByColumnAndRow(
                $index,
                self::$Prow,
                $item
            );
        }
    }


    /**
     * 设置内容二维数组
     */
    public function content($list = []): Excel
    {
        foreach ($list as $item) {
            self::$Prow++;
            foreach (self::$list_key as $k => $v) {
                self:: $worksheet->setCellValueByColumnAndRow(
                    $k + 1,
                    self::$Prow,
                    $item[$v] ?? ''
                );
            }
        }

        return $this;
    }

    /**
     * 导出
     *
     * @param  string  $writerType  导出类 Xls,Xlsx,Ods,Csv,Html,Tcpdf,Dompdf,Mpdf
     * @param  bool  $browser  是否浏览器导出  浏览器|信息流
     * @param  string  $filename  保存文件名
     *
     * @return void
     */
    public function save(
        string $writerType = 'Xlsx',
        bool $browser = true,
        string $filename = 'php://output'
    ) {
        if ($browser) {
            header(
                'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            header(
                'Content-Disposition: attachment;filename='.self:: $title.'.'
                .strtolower($writerType)
            );
            header('Cache-Control: max-age=0');
        }
        $write = IOFactory::createWriter(self::$spreadsheet, $writerType);
        $write->save($filename);
    }

    /**
     * 获取Excel文本数据
     *
     * @param  string  $filePath  文件信息
     * @param  array  $cols  设置字段 从A开始 ['id','name','content']
     * @param  string  $readerType  解析类 Xlsx,Xls,Xml,Ods,Slk,Gnumeric,Html,Csv
     * @param  int  $start  开始行数
     * @param  int  $sheetIndex  sheet索引页
     *
     * @return array|string
     */
    public static function getFileData(
        string $filePath,
        array $cols = [],
        string $readerType = 'Xlsx',
        int $start = 2,
        int $sheetIndex = 0
    ) {
        $objReader = IOFactory::createReader($readerType);//设置类型的读取器
        $objReader->setReadDataOnly(true);
        $spreadsheet = $objReader->load($filePath); //载入excel表格
//      $sheetCount =  $spreadsheet->getSheetCount(); //获取sheet索引 总数
        $worksheet  = $spreadsheet->getSheet($sheetIndex);
        $highestRow = $worksheet->getHighestRow(); // 总行数
        if ($highestRow - $start < 0) {
            return 'Excel文件中无有效数据';
        }
        $data = [];
        for ($row = $start; $row <= $highestRow; $row++) {
            foreach ($cols as $key => $val) {
                $data[$row][$val] = $worksheet->getCellByColumnAndRow(
                    $key + 1,
                    $row
                )->getValue();
            }
        }

        return array_values($data);
    }
}