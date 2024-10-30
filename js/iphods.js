// tagged version 8.14

function trackOutboundLink(link, category, action, label, value) {
 
try {
_gaq.push(['_trackEvent', category , action, label, value]);
} catch(err){}
setTimeout(function() {
document.location.href = link.href;
}, 100);
}