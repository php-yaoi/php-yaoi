<?php

namespace Yaoi;
class Debug
{
    const TRACE_HTML = 'html';
    const TRACE_FILE = 'file';
    const TRACE_TEXT = 'text';

    const LOG = 'debug';

    public static $errorLevels = array(
        E_ERROR => 'error',
        E_WARNING => 'warning',
        E_PARSE => 'parse',
        E_NOTICE => 'notice',
        E_CORE_ERROR => 'core-error',
        E_CORE_WARNING => 'core-warning',
        E_COMPILE_ERROR => 'compile-error',
        E_COMPILE_WARNING => 'compile-warning',
        E_USER_ERROR => 'user-error',
        E_USER_WARNING => 'user-warning',
        E_USER_NOTICE => 'user-notice',
        E_STRICT => 'strict',
        E_RECOVERABLE_ERROR => 'recoverable-error',
        E_DEPRECATED => 'deprecated',
        E_USER_DEPRECATED => 'user-deprecated',
        E_ALL => 'all',
    );


    public static $isActive = false;

    public static function backTrace($skip = 0, $return = self::TRACE_HTML)
    {
        //if (!self::$isActive) {
//            return '';
//        }

        $trace = debug_backtrace();
        $result = array(
            self::TRACE_HTML => '',
            self::TRACE_TEXT => '',
            self::TRACE_FILE => '',
        );

        for ($i = $skip; $i < count($trace); $i++) {
            $t = $trace[$i];
            if (isset($t['class'])) $t['function'] = $t['class'] . $t['type'] . $t['function'];
            if (!isset($t['file'])) $t['file'] = '';
            if (!isset($t['line'])) $t['line'] = '';
            $ta = array();
            if (!isset($t['args'])) {
                $t['args'] = array();
            }
            foreach ($t['args'] as $a) {
                if (is_object($a)) $ta[] = substr(str_replace(array("\n", "\r"), array(''), print_r($a, 1)), 0, 250);
                elseif (is_array($a)) $ta[] = substr(str_replace(array("\n", "\r"), array(''), print_r($a, 1)), 0, 250);
                else $ta[] = $a;
            }

            $t['file'] = str_replace($_SERVER['DOCUMENT_ROOT'], '.', $t['file']);

            $result[self::TRACE_TEXT] .= "$t[function]('" . implode("','", $ta) . "') @ $t[file]:$t[line],\n";
            $result[self::TRACE_HTML] .= "<span title=\"$t[file]:$t[line]\">$t[function]('" . implode("','", $ta) . "')</span>,<br />";
            $result[self::TRACE_FILE] .= "$t[function]@$t[file]:$t[line],";
        }

        $result[self::TRACE_HTML] = "<a href=\"#\" onclick=\"this.nextSibling.style.display='block';this.style.display='none';return false\">...</a><div style=\"display:none\">" .
            $result[self::TRACE_HTML] . '</div>';
        return $return ? $result[$return] : $result;
    }

}