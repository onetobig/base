<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 10:12
 */

namespace App\Api\Helpers;

class LanHuTranslator
{
    protected $res = [];

    public static function run($info)
    {
        $info = str_replace("\r\n", "\n", $info);

        if (strpos($info, 'UILabel') !== false) {
            $res = self::checkForTextByOC($info);
        } else {
            $res = self::checkForImageByOC($info);
        }
        return $res;
    }

    protected static function checkForImageByOC($info)
    {
        $lines = explode("\n", $info);
        $res = static::getInitRes();
        foreach ($lines as $line) {
            if (strlen($line) == 0)
                continue;
            self::checkLineForColor_OC($line, $res, false);
            self::checkLineForPosition_OC($line, $res);
        }
        return $res;
    }

    protected static function getInitRes()
    {
        return [
            'left' => 0,
            'top' => 0,
            'width' => 0,
            'height' => 0,
            'font_name' => 0,
            'font_weight' => 0,
            'font_size' => 0,
            'font_color' => '#000000',
            'text' => '',
            'text_align' => 'left',
            'left_init' => false,
            'top_init' => false,
            'text_init' => false,
            'color_init' => false,
            'width_init' => false,
            'height_init' => false,
            'times' => 0,
        ];
    }

    /**
     * 检查文字信息
     *
     * @param [type] $info
     * @return void
     */
    protected static function checkForTextByOC($info)
    {
        $lines = explode("\n", $info);
        $res = static::getInitRes();
        foreach ($lines as $line) {
            if (strlen($line) == 0)
                continue;
            self::checkLineForFont_OC($line, $res);
            self::checkLineForPosition_OC($line, $res);
            self::checkLineForText_OC($line, $res);
            self::checkLineForColor_OC($line, $res, false);
        }

        // 没有识别到文字
        $line_feed = false;
        if (!$res['text']) {
            self::checkInfoForText_OC($info, $res);
            if ($res['text']) $line_feed = true;
        }

        // 判断居中
        $align = (640 - $res['width']) / 2 - $res['left'];
        if ($align > -5 && $align < 5) {
            $res['text_align'] = 'center';
        }

        if ($res['text_align'] === 'center' &&  $res['times'] < 2 && !$line_feed) {
            $res['left'] = 0;
            $res['width'] = 640;
        }

        // 不是居中，不是换行，不是多段代码
        if ($res['text_align'] !== 'center' && !$line_feed && $res['times'] < 2) {
            $res['width'] = 640 - $res['left'];
        }

        if ($res['times'] >= 2) {
            $res['text_align'] = 'center';
        }

        return $res;
    }

    /**
     * 正则检查文字信息
     */
    protected static function checkInfoForText_OC($info, array &$res)
    {
        $index = strpos($info, "[NSMutableAttributedString alloc] initWithString:@");
        if ($index != -1) {
            $index1 = strpos($info, "initWithString:@");
            $index2 = strpos($info, "\" attributes:@");
            if ($index1 + 17 < $index2 && $index1 > 0) {
                $text = substr($info, $index1 + 17, $index2 - $index1 - 17);
                if ($text) {
                    $texts = explode("\n", $text);
                    $res['height'] = ($res['height']) / count($texts);
                    $res['text'] = implode("", $texts);
                }
            }
        }
    }

    /**
     * 检查文字信息
     *
     * @param string $line
     * @param array $info
     * @return void
     */
    protected static function checkLineForText_OC(string $line, array &$info)
    {
        $index = strpos($line, "[NSMutableAttributedString alloc] initWithString:@");
        if ($index != -1) {
            $index1 = strpos($line, "initWithString:@");
            $index2 = strpos($line, "\" attributes:@");
            if ($index1 + 17 < $index2 && $index1 > 0) {
                $info['text'] = substr($line, $index1 + 17, $index2 - $index1 - 17);
                $info['text_init'] = true;
            }
        }
    }
    /**
     * 检查位置信息
     *
     * @param string $line
     * @param array $info
     * @return void
     */
    protected static function checkLineForPosition_OC(string $line, array &$info)
    {
        $index = strpos($line, "CGRectMake(");
        if ($index != -1) {
            $index1 = strpos($line, '(');
            $index2 = strpos($line, ')');
            if ($index1 < $index2 && $index1 > 0) {
                $pos_str = substr($line, $index1 + 1, $index2 - $index1 - 1);
                $pos_infos = explode(',', $pos_str);
                if (count($pos_infos) == 4) {
                    $ori_info = $info;
                    $left = trim($pos_infos[0]) * 2;
                    $top = trim($pos_infos[1]) * 2;
                    $width = trim($pos_infos[2]) * 2;
                    $height = trim($pos_infos[3]) * 2;
                    // x 坐标
                    $info = array_merge($info, compact('left', 'top', 'width', 'height'));
                    if ($info['left_init'] && $ori_info['left'] < $left) {
                        $info['left'] = $ori_info['left'];
                    }
                    $info['left_init'] = true;

                    // y 坐标
                    if ($info['top_init'] && $ori_info['top'] > $top) {
                        $info['top'] = $ori_info['top'];
                    }
                    $info['top_init'] = true;

                    // 宽
                    if ($info['width_init'] && $ori_info['width'] > $width) {
                        $info['width'] = $ori_info['width'];
                    }
                    $info['width_init'] = true;

                    // 高
                    if ($info['height_init'] && $ori_info['height'] < $height) {
                        $info['height'] = $ori_info['height'];
                    }
                    $info['height_init'] = true;
                    $info['times'] += 1;
                }
            }
        }
    }

    /**
     * 检查字体信息
     *
     * @param string $line
     * @param array $info
     * @return void
     */
    protected static function checkLineForFont_OC(string $line, array &$info)
    {
        $index = strpos($line, 'UIFont fontWithName');
        if ($index !== false) {
            $index1 = strpos($line, 'fontWithName:@');
            $index2 = strrpos($line, '" size:');
            if ($index1 < $index2 && $index1 > 0) {
                $font_str = substr($line, $index1 + 15, $index2 - $index1 - 15);
                if (strpos($font_str, '-') != 1) {
                    $font_elements = explode('-', $font_str);
                    if (count($font_elements) == 2) {
                        $info['font_name'] = $font_elements[0];
                        $info['font_weight'] = $font_elements[1];
                    }
                }
            }

            $index1 = strpos($line, "size:");
            $index2 = strpos($line, "],NSForegroundColorAttributeName");
            if ($index1 + 6 < $index2 && $index1 > 0) {
                $font_size_str = substr($line, $index1 + 6, $index2 - $index1 - 6);
                $info['font_size'] = (float)$font_size_str * 2;
            }
        }
    }

    /**
     * 检查颜色信息
     *
     * @param string $line
     * @param array $info
     * @param boolean $for_image
     * @return void
     */
    protected static function checkLineForColor_OC(string $line, array &$info, bool $for_image = false)
    {
        $index = strpos($line, '[UIColor');
        if ($index !== false) {
            $red_str = '';
            $green_str = '';
            $blue_str = '';
            $index1 = strpos($line, 'colorWithRed:');
            $index2 = $for_image ? strpos($line, '];') : strrpos($line, ']}]');
            if ($index1 < $index2 && $index1 > 0) {
                $color_str = substr($line, $index1, $index2 - $index1);
                $color_str = str_replace('NaN', '0', $color_str);
                $color_infos = explode(' ', $color_str);
                if (count($color_infos) == 4) {
                    $tmp = 0;
                    $color_info = trim($color_infos[0]);
                    $end_index = strpos($color_info, '/');
                    if ($end_index > 13) {
                        $tmp = substr($color_info, 13, $end_index - 13) ?? 0;
                        $red_str = strval(dechex($tmp));
                        $red_str = str_pad($red_str, 2, '0', STR_PAD_LEFT);
                    }

                    $tmp = 0;
                    $color_info = trim($color_infos[1]);
                    $end_index = strpos($color_info, '/');
                    if ($end_index > 6) {
                        $tmp = substr($color_info, 6, $end_index - 6) ?? 0;
                        $green_str = dechex($tmp);
                        $green_str = str_pad($green_str, 2, '0', STR_PAD_LEFT);
                    }

                    $tmp = 0;
                    $color_info = trim($color_infos[2]);
                    $end_index = strpos($color_info, '/');
                    if ($end_index > 5) {
                        $tmp = substr($color_info, 5, $end_index - 5) ?? 0;
                        $blue_str = dechex($tmp);
                        $blue_str = str_pad($blue_str, 2, '0', STR_PAD_LEFT);
                    }
                }

                if ($info['text_init']) {
                    $info['font_color'] = "#" . $red_str . $green_str . $blue_str;
                }
            }
        }
    }
}
