define([
    'jquery',
    'core/str',
    'core/notification',
    'block_course_statistics/repository-lazy'
], function (
    $,
    str,
    notification,
    Repository
) {

    var pausemeasure = function () {
        var buttons = document.querySelectorAll('.measurements');
        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                var idParts = button.id.split('_');
                var action = idParts[0]; // Assuming the action is the first part of the id
                var courseid = idParts[1]; // Assuming the courseid is the second part of the id
                var status = button.getAttribute('status');
                var args = {
                    courseid: courseid,
                    status: status
                };
                Repository.pausemeasurestatus(args).then(function (status) {
                    if (status == 1) {
                        button.classList.remove('btn-success');
                        button.classList.add('btn-warning');
                        button.setAttribute('status', '0');
                        button.textContent = M.util.get_string('pausemeasure', 'block_course_statistics');

                    } else {
                        button.classList.remove('btn-warning');
                        button.classList.add('btn-success');
                        button.setAttribute('status', '1');
                        button.textContent = M.util.get_string('startmeasure', 'block_course_statistics');
                    }

                });
            });
        });
    };


    return {
        pausemeasure: pausemeasure
    };

});



