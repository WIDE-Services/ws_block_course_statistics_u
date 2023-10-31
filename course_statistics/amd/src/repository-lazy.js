define([
    'jquery',
    'core/ajax',
    'core/notification'
], function (
    $,
    Ajax,
    Notification
) {


    var pausemeasurestatus = function (args) {
        var request = {
            methodname: 'block_course_statistics_pause_measure',
            args: args
        };

        var promise = Ajax.call([request])[0];

        promise.fail(Notification.exception);

        return promise;
    };

    return {
        pausemeasurestatus: pausemeasurestatus
    };
});