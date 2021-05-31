<?php
/**
 * @author    Ewave <https://ewave.com/>
 * @copyright 2018-2019 NASKO TRADING PTY LTD
 * @license   https://ewave.com/wp-content/uploads/2018/07/eWave-End-User-License-Agreement.pdf BSD Licence
 */

namespace CoreDevBoxScripts\Library;

/**
 * Class JsonConfigPatterns
 * @package CoreDevBoxScripts\Library
 */
class JsonConfigPatterns
{
    /**
     * @return array
     */
    protected static function getPatterns()
    {
        return [
            '[~website_root]' => EnvConfig::getValue('WEBSITE_APPLICATION_ROOT') ?: EnvConfig::getValue('WEBSITE_DOCUMENT_ROOT'),
            '[~temp_storage]' => JsonConfig::getConfig('base_params->temp_storage->base', false, false)
        ];
    }

    /**
     * @param string|array $value
     * @return string
     */
    public static function scroll($value)
    {
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = self::scroll($subValue);
            }
        } else {
            $patterns = self::getPatterns();
            foreach ($patterns as $k => $v) {
                if (strpos($value, $k) !== false) {
                    $newVal = str_replace($k, $v, $value);
                    return $newVal;
                }
            }
        }
        return $value;
    }
}
