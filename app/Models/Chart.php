<?php

namespace App\Models;

class Chart
{
    public static function createContext($name, $width = 'default', $height = 'default')
    {
        if ($width != 'default' && $height != 'default') {
            return "<canvas width='$width' height='$height' id='$name'></canvas>";
        }

        return "<canvas id='$name'></canvas>";
    }

    public static function create($name, $labels, $datasets, $options)
    {
        $chart = '
			'.$name."_chart = new Chart(document.getElementById('$name').getContext(\"2d\"), {
			type: 'line',
			data: {
            labels: [ ".implode(', ', $labels).' ],';

        $chart .= 'datasets: [';
        foreach ($datasets as $d) {
            $chart .= $d->get();
        }
        $chart .= "]
			},
				options: \"$options\",
			});
		";

        return $chart;
    }

    public static function apexFromOptions($options)
    {

        //$options = json_encode($options);
        $ret = '';
        $ret .= '<div id="'.$options['div']."\"></div>\n";
        $ret .= "<script type=\"text/javascript\">\n\n";

        $ret .= "$(document).ready(function(){ \n\n";
        $ret .= 'var '.$options['div'].'_data = '.self::json_encode_advanced($options).";\n\n";
        $ret .= 'var '.$options['div'].'_chart = new ApexCharts(document.querySelector("#'.$options['div'].'"), '.$options['div']."_data);\n\n";
        $ret .= ''.$options['div']."_chart.render();\n\n";
        //$ret .= "alert(js_data.series[0].data[0])";
        $ret .= "});\n";

        $ret .= "</script>\n\n";

        return $ret;
    }

    public static function apexDonutChart($div, $data)
    {
        $options = [
            'chart' => [
                'type' => "'pie'",
                'height' => 350,
            ],
            'series' => $data,
            'labels' => ["'iOS'", "'Android'", "'Browser'"],
            'responsive' => [
                [
                    'breakpoint' => 480,
                    'options' => [
                        'chart' => [
                            'width' => 100,
                            'height' => 350,
                        ],
                        'legend' => [
                            'position' => "'bottom'",
                        ],
                    ],
                ],
            ],
        ];

        $options['div'] = $div;

        return self::apexFromOptions($options);
    }

    public static function apexMultipleYAxis($div, $title, $data, $extra = null)
    {
        $series = $data['series'];
        $seriesyaxis = $data['seriesyaxis'];
        $categories = $data['categories'];

        $options = [
            'chart' => [
                'height' => 350,
                'type' => "'line'",
                'stacked' => false,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => $series,
            'stroke' => [
                'width' => [1, 1, 4],
            ],
            'title' => [
                'text' => "'$title'",
                'align' => "'left'",
                'offsetX' => 110,
            ],
            'xaxis' => [
                'categories' => $categories,
            ],
            'yaxis' => [
                $seriesyaxis,
            ],
            'tooltip' => [
                'fixed' => [
                    'enabled' => true,
                    'position' => "'topLeft'",
                    'offsetX' => 60,
                    'offsetY' => 30,
                ],
            ],
            'legend' => [
                'horizontalAlign' => "'left'",
                'offsetX' => 40,
            ],

        ];

        if ($extra != null) {
            foreach ($extra as $k => $v) {
                if (! isset($options[$k])) {
                    $options[$k] = $v;
                } else {
                    $old = $options[$k];
                    $new = $v;

                    if (is_array($old) && is_array($new)) {
                        foreach (self::arrayRecursiveDiff($new, $old) as $j => $diff) {
                            $options[$k][$j] = $diff;
                            //echo "Setting: options[$k][$j] = " . serialize($diff) . '<br/>';
                        }
                    }
                }
            }
        }

        $options['div'] = $div;

        return self::apexFromOptions($options);
    }

    public static function apexTimeSeries($div, $title, $series_name, $min_date, $data, $extra = null, $extra_series = [])
    {
        $options = [
            'chart' => [
                'type' => "'area'",
                'height' => 200,
                'zoom' => [
                    'enabled' => true,
                ],
            ],
            'title' => [
                'text' => "'$title'",
                'align' => "'left'",
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'series' => [
                [
                    'data' => $data,
                    'name' => "'$series_name'",
                ],
            ],
            'markers' => [
                'size' => 0,
                'style' => "'hollow'",
            ],
            'xaxis' => [
                'type' => "'datetime'",
                'min' => $data[0][0],
                'tickAmount' => 6,
            ],
            'tooltip' => [
                'x' => [
                    'format' => "'MM/yy'",
                ], //
            ],
            'fill' => [
                'type' => "'gradient'",
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.9,
                    'stops' => '[0, 100]',
                ],
            ],
            'responsive' => [
                [
                    'breakpoint' => "'undefined'",
                    'options' => [],
                ],
            ],
        ];

        if (count($extra_series) > 0) {
            foreach ($extra_series as $k => $series) {
                array_push($options['series'], $series);
            }
        }

        if ($extra != null) {
            foreach ($extra as $k => $v) {
                if (! isset($options[$k])) {
                    $options[$k] = $v;
                } else {
                    $old = $options[$k];
                    $new = $v;

                    if (is_array($old) && is_array($new)) {
                        foreach (self::arrayRecursiveDiff($new, $old) as $j => $diff) {
                            $options[$k][$j] = $diff;
                            //echo "Setting: options[$k][$j] = " . serialize($diff) . '<br/>';
                        }
                    }
                }
            }
        }

        $options['div'] = $div;

        return self::apexFromOptions($options);
    }

    /**
        Private functions
     **/

    // Turn PHP Array into JS Object
    private static function json_encode_advanced(array $arr, $sequential_keys = false, $quotes = false, $beautiful_json = false)
    {
        $output = self::isAssoc($arr) ? '{' : '[';
        $count = 0;
        foreach ($arr as $key => $value) {
            if (self::isAssoc($arr) || (! self::isAssoc($arr) && $sequential_keys == true)) {
                $output .= ($quotes ? '"' : '').$key.($quotes ? '"' : '').' : ';
            }

            if (is_array($value)) {
                $output .= self::json_encode_advanced($value, $sequential_keys, $quotes, $beautiful_json);
            } elseif (is_bool($value)) {
                $output .= ($value ? 'true' : 'false');
            } elseif (is_numeric($value)) {
                $output .= $value;
            } else {
                $output .= ($quotes || $beautiful_json ? '"' : '').$value.($quotes || $beautiful_json ? '"' : '');
            }

            if (++$count < count($arr)) {
                $output .= ', ';
            }
        }

        $output .= self::isAssoc($arr) ? '}' : ']';

        return $output;
    }

    // Helper function for json_encode_advanced()
    private static function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    //
    public static function arrayRecursiveDiff($aArray1, $aArray2)
    {
        $aReturn = [];

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = self::arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) {
                        $aReturn[$mKey] = $aRecursiveDiff;
                    }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }

        return $aReturn;
    }
}
