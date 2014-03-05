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
    (function(){
        var ss=document.getElementsByTagName('script');var c = (ss[ss.length-1]).previousSibling;

        function prepareString(s) {
            return s.replace(/(Array|Object)\n(\s*)\(/g,
                    '<span class="debug-controls"><a class="toggle-display" href="#">$1</a> ' +
                        '<a class="toggle-children" href="#" title="toggle children">\\</a> ' +
                        '<a class="toggle-recursive" href="#" title="toggle recursive">*</a> ' +
                        '</span><span class="debug-data" style="display:none"> ' +
                        '\n$2(')
                .replace(/\n(\s*?)\)\n/g, '\n$1)\n</span>');
        }

        function toggleDisplay(e, show) {
            if ('undefined' == typeof show) {
                show = autoShow(e, 0);
            }

            e.style.display = show ? '' : 'none';
            return show;
        }

        function autoShow(e, level) {
            var show;
            if (!level) {
                return e.style.display == 'none';
            }
            else {
                for (var i = 0; i < e.childNodes.length; ++i) {
                    if ('debug-data' == e.childNodes[i].className) {
                        show = autoShow(e.childNodes[i], level - 1);
                        if (-1 != show) {
                            return show;
                        }
                    }
                }
            }
            return -1;
        }


        function toggleChildren(e, recursive, show) {
            if ('undefined' == typeof show) {
                show = autoShow(e, recursive ? 2 : 1);
                if (-1 == show) {
                    if (recursive) {
                        toggleChildren(e, false);
                        return;
                    }
                    else {
                        toggleDisplay(e);
                        return;
                    }
                }
            }
            for (var i = 0; i < e.childNodes.length; ++i) {
                if ('debug-data' == e.childNodes[i].className) {
                    toggleDisplay(e.childNodes[i], show);
                    if (recursive) {
                        toggleChildren(e.childNodes[i], true, show);
                    }
                }
            }
            if (show) {
                toggleDisplay(e, show);
            }
        }

        function prepare(e) {
            e.innerHTML = prepareString(e.innerHTML);
            var a = e.getElementsByTagName('a');
            for (var i = 0; i < a.length; ++i) {
                a[i].onclick = function() {
                    var cls = this.className, element = this.parentNode.nextSibling;
                    if (cls == 'toggle-display') {
                        toggleDisplay(element);
                    }
                    else if (cls == 'toggle-children') {
                        toggleChildren(element, false);
                    }
                    else if (cls == 'toggle-recursive') {
                        toggleChildren(element, true);
                    }


                    return false;
                };
            }
        }

        prepare(c);

    })();
</script>
    <?php
    }
}