!function(e,d){"object"==typeof exports&&"undefined"!=typeof module?module.exports=d(require("dayjs")):"function"==typeof define&&define.amd?define(["dayjs"],d):e.dayjs_locale_da=d(e.dayjs)}(this,function(e){"use strict";e=e&&e.hasOwnProperty("default")?e.default:e;var d={name:"da",weekdays:"søndag_mandag_tirsdag_onsdag_torsdag_fredag_lørdag".split("_"),months:"januar_februar_marts_april_maj_juni_juli_august_september_oktober_november_december".split("_"),weekStart:1,ordinal:function(e){return e+"."},formats:{LT:"HH:mm",LTS:"HH:mm:ss",L:"DD.MM.YYYY",LL:"D. MMMM YYYY",LLL:"D. MMMM YYYY HH:mm",LLLL:"dddd [d.] D. MMMM YYYY [kl.] HH:mm"},relativeTime:{future:"om %s",past:"%s siden",s:"få sekunder",m:"et minut",mm:"%d minutter",h:"en time",hh:"%d timer",d:"en dag",dd:"%d dage",M:"en måned",MM:"%d måneder",y:"et år",yy:"%d år"}};return e.locale(d,null,!0),d});
