<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

class Directory
{
    /**
     * Define whether dir is empty
     *
     * @param $dir
     * @return bool
     */
    public static function isEmptyDir($dir)
    {
        $dir = trim($dir, '/');
        return !count(glob("/$dir/*"));
    }

    /**
     * Define whether dir is empty
     *
     * @param $dir
     * @return bool
     */
    public static function isDir($path)
    {
        $path = trim($path, '/');
        return is_dir("/$path");
    }
}
