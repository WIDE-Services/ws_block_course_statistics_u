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
                console.log('Action:', action);

                var courseid = idParts[1]; // Assuming the courseid is the second part of the id
                console.log('Course ID:', courseid);
                    console.log(courseid); // Check if courseid is being retrieved
                    var status = button.getAttribute('status');
                    var args = {
                        courseid: courseid,
                        status: status
                    };
                    Repository.pausemeasurestatus(args).then(function (status) {
                        console.log(status);

                        if (status == 1) {
                            button.classList.remove('btn-success');
                            button.classList.add('btn-warning');
                            button.setAttribute('status', '0');

                        } else {
                            button.classList.remove('btn-warning');
                            button.classList.add('btn-success');
                            button.setAttribute('status', '1');

                        }

                    });
                });
            });
        };


    return {
        pausemeasure: pausemeasure
    };

});



