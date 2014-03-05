<?php

/**
 * Class Debug_CollapsiblePrintR
 * @method static Debug_CollapsiblePrintR create($var)
 */
class Debug_CollapsiblePrintR extends View_HTML_Element {
    protected $tag = 'pre';

    public function __construct($var) {
        $this->content = print_r($var, 1);
    }


    public function renderTail() {
        parent::renderTail();
        ?>
<script>
    var phpPrintRBeautifier = {
        prepareString: function (s) {
            return s.replace(/(Array|Object)\n(\s*)\(/g,
                    '<span class="debug-controls"><a class="toggle-display" href="#" onclick="phpPrintRBeautifier.toggleDisplay(this.parentNode.nextSibling);return false;">$1</a> ' +
                        '<a class="toggle-children" href="#" onclick="phpPrintRBeautifier.toggleChildren(this.parentNode.nextSibling, false);return false" title="toggle children">\\</a> ' +
                        '<a class="toggle-recursive" href="#" onclick="phpPrintRBeautifier.toggleChildren(this.parentNode.nextSibling, true);return false;" title="toggle recursive">*</a> ' +
                        '</span><span class="debug-data" style="display:none"> ' +
                        '\n$2(')
                .replace(/\n(\s*?)\)\n/g, '\n$1)\n</span>');
        },

        autoShow: function (e, level) {
            var show;
            if (!level) {
                return e.style.display == 'none';
            }
            else {
                for (var i = 0; i < e.childNodes.length; ++i) {
                    if ('debug-data' == e.childNodes[i].className) {
                        show = this.autoShow(e.childNodes[i], level - 1);
                        if (-1 != show) {
                            return show;
                        }
                    }
                }
            }
            return -1;
        },


        toggleDisplay: function (e, show) {
            if ('undefined' == typeof show) {
                show = this.autoShow(e, 0);
            }

            e.style.display = show ? '' : 'none';
            return show;
        },

        toggleChildren: function (e, recursive, show) {
            if ('undefined' == typeof show) {
                show = this.autoShow(e, recursive ? 2 : 1);
                if (-1 == show) {
                    if (recursive) {
                        return this.toggleChildren(e, false);
                    }
                    else {
                        return this.toggleDisplay(e);
                    }
                }
            }
            for (var i = 0; i < e.childNodes.length; ++i) {
                if ('debug-data' == e.childNodes[i].className) {
                    this.toggleDisplay(e.childNodes[i], show);
                    if (recursive) {
                        this.toggleChildren(e.childNodes[i], true, show);
                    }
                }
            }
            if (show) {
                this.toggleDisplay(e, show);
            }
        },

        prepare: function (e) {
            e.innerHTML = this.prepareString(e.innerHTML);
            var a = e.getElementsByTagName('a');
            for (var i = 0; i <= a.length; ++i) {
                var c = a[i].className;
                if (c == 'toggle-display') {
                    a[i].click = function(e) {
                        phpPrintRBeautifier.toggleDisplay(this.parentNode.nextSibling);
                        return false;
                    }
                }
            }
        }
    };
</script>
    <?php
    }
}