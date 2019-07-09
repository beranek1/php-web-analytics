var d = new Date();
d.setTime(d.getTime() + (180*24*60*60*1000));
var expires = "expires="+d.toUTCString();
var device = {};
device.screen_width = screen.width;
device.screen_height = screen.height;
device.interface_width = (screen.width - screen.availWidth);
device.interface_height = (screen.height - screen.availHeight);
device.color_depth = screen.colorDepth;
device.pixel_depth = screen.pixelDepth;
document.cookie = "device_profile=" + JSON.stringify(device) + "; " + expires + "; path=/";
var browser = {};
browser.cookies_enabled = navigator.cookieEnabled;
browser.java_enabled = navigator.javaEnabled();
document.cookie = "browser_profile=" + JSON.stringify(browser) + "; " + expires + "; path=/";
if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (position) {
        var location = {};
        location.latitude = position.coords.latitude;
        location.longitude = position.coords.longitude;
        location.altitude = position.coords.altitude;
        document.cookie = "geolocation=" + JSON.stringify(location) + "; " + expires + "; path=/";
    });
}