!function(t,e){"object"==typeof exports&&"undefined"!=typeof module?module.exports=e():"function"==typeof define&&define.amd?define(e):t.dayjs_plugin_localizedFormat=e()}(this,function(){"use strict";return function(t,e,o){var n=e.prototype,Y=n.format,m={LTS:"h:mm:ss A",LT:"h:mm A",L:"MM/DD/YYYY",LL:"MMMM D, YYYY",LLL:"MMMM D, YYYY h:mm A",LLLL:"dddd, MMMM D, YYYY h:mm A"};o.en.formats=m,n.format=function(t){var e=this.$locale().formats||{},o=(t||"YYYY-MM-DDTHH:mm:ssZ").replace(/LTS|LT|L{1,4}/g,function(t){return e[t]||m[t]});return Y.call(this,o)}}});
