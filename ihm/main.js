/*********************************************************************
 *           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE             *
 *                   Version 2, December 2004                        *
 *                                                                   *
 *  Copyleft meh.                                                    *
 *                                                                   *
 *           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE             *
 *  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION  *
 *                                                                   *
 *  0. You just DO WHAT THE FUCK YOU WANT TO.                        *
 *********************************************************************/

miniLOL.module.create('ihm', {
    version: '0.1',

    type: 'passive',

    initialize: function () {
        this.execute(23);
    },

    execute: function (seconds) {
        if (!Prototype.Browser.IE) {
            return;
        }

        var block = new Element("div", { style: "width: 100%; height: 100%; z-index: 9001; position: absolute; top: 0; left: 0; background: black; color: white; font-weight: bold; font-size: 23px; text-align: center; opacity: 0.9; filter: alpha(opacity = 90);"});

        document.body.appendChild(block);
        block.update("<div style='position: relative; top: 48%;'>" +
            "Are you aware that you're using a SHITTY browser?<br/><br/>" +
            "If you aren't, get a real browser, get <a href='http://getfirefox.com' style='font-size: 23px;'>Firefox</a>.<br/><br/>" +
            "If you are, <span style='color: red;'>yiff in hell furfag</span>."
        + "</div>");

        setTimeout(function () {
            block.parentNode.removeChild(block);            
        }, seconds * 1000);
    }
});