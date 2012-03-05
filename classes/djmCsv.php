<?php /*
Copyright (c) 2010-2012 Dave James Miller

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. */

/**
 * @author Dave James Miller
 * @copyright Copyright (c) 2010-2012 Dave Miller
 * @license http://davejamesmiller.com/mit-license MIT License
 */

class djmCsv
{

    public static function headers($options = array())
    {
        $options = array_merge(array(
            'filename' => null,
        ), $options);

        header('Content-Type: text/csv');
        ini_set('html_errors', false);

        if ($options['filename']) {
            header('Content-Disposition: attachment; filename=' . $options['filename']);
        }
    }

    public static function generateRow($row, $options = array())
    {
        $options = array_merge(array(
            'delimiter' => ',',
            'enclosure' => '"',
            'escape'    => '"',
            'excel'     => false,
        ), $options);

        $output = '';
        $firstCol = true;

        foreach ($row as $col) {
            if ($firstCol) {
                $firstCol = false;
            } else {
                $output .= $options['delimiter'];
            }

            if ($options['excel']) {
                if (is_string($col) && is_numeric($col)) {
                    // Excel hack to display phone numbers correctly!
                    // http://www.creativyst.com/Doc/Articles/CSV/CSV01.htm
                    $output .= '=';
                }
            }

            $output .= $options['enclosure']
                    .  str_replace($options['enclosure'], $options['escape'] . $options['enclosure'], $col)
                    .  $options['enclosure'];
        }

        return $output;
    }

    public static function generate($data, $options = array())
    {
        $options = array_merge(array(
            'headings' => false,
        ), $options);

        $output = '';
        $firstRow = true;

        foreach ($data as $row) {
            if ($firstRow) {
                if ($options['headings']) {
                    if (is_array($options['headings'])) {
                        $headings = $options['headings'];
                    } elseif (is_object($row) && method_exists($row, 'toArray')) {
                        $headings = array_keys($row->toArray());
                    } else {
                        $headings = (array) array_keys($row);
                    }
                    $output .= self::generateRow($headings, $options);
                    $output .= "\n";
                }
                $firstRow = false;
            } else {
                $output .= "\n";
            }

            $output .= self::generateRow($row, $options);
        }

        return $output;
    }

    public static function output($data, $options = array())
    {
        // Generate first so any exceptions are shown in the browser not in the file
        $data = self::generate($data, $options);
        self::headers($options);
        echo $data;
    }

}
