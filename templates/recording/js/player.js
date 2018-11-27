$(function() {

    let continuousPlaySelector = $("input[name=continuous_play]");

    isContinuous = continuousPlaySelector.prop('checked');
    isDirectStart = false;
    estimateDistID = $("input[name=estimateDistID]").val();
    selectionDuration = maxTime - minTime;

    if (estimateDistID && estimateDistID > 0) {
        isDirectStart = true;
    }

    continuousPlaySelector.on('click', function () {
        isContinuous = this.checked;
    });

    $('#stop').click(function() {
        setContinuousPlay(false);
    });

    continuousPlay = function() {
        if (fileDuration > maxTime) {
            toggleLoading("Loading...");
            $('#x').val(maxTime);
            $('#w').val(maxTime + selectionDuration);
            $('#recordingForm').submit();
        }
    };

    setContinuousPlay = function(value) {
        isContinuous = value;
        continuousPlaySelector.attr('checked', value);
    };

    savePlayLog = function() {
        $.post(base_url + "/ajaxcallmanager.php?class=PlayLog&action=save",
            {
                recordingId: recordingId,
                userId: userId,
                startTime: getCookie('playStartTime'),
                stopTime: new Date().valueOf() / 1000,
            })
            .fail(function(xhr, textStatus, errorThrown) {
                console.log('Error while saving play log: ' + xhr.responseText);
            })
            .done(function(data) {
                if(data.error) {
                    console.log('Error while saving play log: ' + data.error);
                }
                deleteCookie('playStartTime');
            });
    };
});
