// Copyright Up and Running

// various includes of the Digium API
var app = require('app');
app.init();
var screen = require('screen');
var util = require('util');

// start objects that will help namespace the running application
appUtils = {};
alert911 = {};


alert911.idelResetCounter = 0;
alert911.isAlertTriggered = false;
alert911.config = {};

// used to send an alert from a phone
alert911.sendAlert = function(extraPayload) {
    util.debug("sending notice of 911 call");

    var payload = {foo: "911"};
    payload.extend(extraPayload);
    var request = new NetRequest();

    request.open('POST',this.getServer() + "/alert");
    request.setRequestHeader('Content-Type','application/json');
    request.oncomplete = function() {
        util.debug("post successfully sent");
    };

    request.send(JSON.stringify(payload));
};

// sets up the softkey navigation when the app is brought to the foreground
alert911.setupSoftKeys = function() {
    window.clearSoftkeys();

    //set the first softkey to allow the user to answer the call
    window.setSoftkey(1, app.t('SendNotice'), function() {
        this.sendAlert();
    }.bind(this));


    window.setSoftkey(2,app.t('Trigger'),function() {
        this.triggerAlert();
    }.bind(this));

    window.setSoftkey(3,app.t('Clear'),function() {
        this.clearAlert();
    }.bind(this));

};

// clears the alert from the phone
alert911.clearAlert = function() {
    this.isAlertTriggered = false;
    alert911Views.alertEnabled = false;
    alert911Views.display();
    digium.background();
};

// triggers the alert action
alert911.triggerAlert = function() {
    util.debug("Triggering 911 Alert @ " + appUtils.getDateTime());
    this.isAlertTriggered = true;
    alert911Views.alertEnabled = true;
    digium.foreground();
};

alert911.app_foreground = function() {
    util.debug("app_foreground is called");
    alert911Views.display();
};


alert911.checkForEmergencyCall = function() {
    var args = Array.prototype.slice.call(arguments, 1);
    util.debug("")
    var call_data = args.join("; ");
    this.sendAlert({data: call_data});
};

// since the version of web sockets isn't avaialble and it's not easy to install something like that on these devices, we using long polling to keep an open connection
// to the server
alert911.startLongPolling = function() {
    var that = this;

    (function poll() {
        var request = new NetRequest();
        request.open('GET',that.getServer() + '/alert');
        request.setTimeout(30000);
        request.onreadystatechange = function () {

            alert911Views.server = that.getServer();
            alert911Views.display();

            if (request.readyState == 4 && request.status == 200) {
                that.triggerAlert();
            }

        };
        request.oncomplete = function () {
            poll();
        }
        request.send();
    })();


};

alert911.getServer = function() {
    var url = "http://" + this.config.settings.host + ":" + this.config.settings.port;
    return url;
};

// sets up the app
alert911.init = function() {
    digium.app.exitAfterBackground = false;


    var config = app.getConfig();
    this.config = config;


    this.setupSoftKeys();
    this.startLongPolling();

    digium.event.observe({
        'eventName'     : 'digium.app.foreground',
        'callback'      : this.app_foreground.bind(this)
    });

    digium.event.observe({
        'eventName': '',
        'callback': this.checkForEmergencyCall.bind(this)
    });
};


appUtils.getDateTime = function (now) {

    now = typeof now !== 'undefined' ? now : new Date();

    var year    = now.getFullYear();
    var month   = now.getMonth()+1;
    var day     = now.getDate();
    var hour    = now.getHours();
    var minute  = now.getMinutes();
    var second  = now.getSeconds();
    if(month.toString().length == 1) {
        var month = '0'+month;
    }
    if(day.toString().length == 1) {
        var day = '0'+day;
    }
    if(hour.toString().length == 1) {
        var hour = '0'+hour;
    }
    if(minute.toString().length == 1) {
        var minute = '0'+minute;
    }
    if(second.toString().length == 1) {
        var second = '0'+second;
    }
    var dateTime = year+'/'+month+'/'+day+' '+hour+':'+minute+':'+second;
    return dateTime;
};

util.debug("starting application @ " + appUtils.getDateTime());
alert911.init();

