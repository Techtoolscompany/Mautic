/** This section is only needed once per page if manually copying **/
if (typeof MauticPrefCenterLoaded === 'undefined') {
    var MauticPrefCenterLoaded = true;

    function replaceSlotParams(slot){
        var i;
        var text = slot.dataset['paramLabelText'];

        if (text) {
            setLabelText(slot, 'label.control-label', text);
            var channels = slot.querySelectorAll('label[data-channel]');
            for (i = 0; i < channels.length; i++) {
                channels[i].innerHTML = text.replace('%channel%', channels[i].dataset['channel']);
            }
        }

        var numOfLabelsInSlot = 4;
        for (i = 1; i <= numOfLabelsInSlot; i++) {
            text = slot.dataset['paramLabelText' + i];
            if (typeof text !== "undefined") {
                setLabelText(slot, 'label.label' + i, text);
            }
        }
        // button value replace
        text = slot.dataset['paramLinkText'];
        if (typeof text !== "undefined") {
            var labels = slot.querySelectorAll('.button');
            labels[0].innerHTML = text;
        }
    }

    function setLabelText(slot, querySelector, text) {
        var labels = slot.querySelectorAll(querySelector);

        for (var i = 0; i < labels.length; i++) {
            labels[i].innerHTML = text;
        }
    }

    // Handler when the DOM is fully loaded
    var callback = function(){
        var slots = document.querySelectorAll('div[data-slot="segmentlist"], div[data-slot="categorylist"], div[data-slot="preferredchannel"], div[data-slot="channelfrequency"],div[data-slot="saveprefsbutton"]');
        for (var i = 0; i < slots.length; i++) {
            replaceSlotParams(slots[i]);
        }
    };

    if (document.readyState === "complete" || !(document.readyState === "loading" || document.documentElement.doScroll)) {
        callback();
    } else {
        document.addEventListener("DOMContentLoaded", callback);
    }

    function togglePreferredChannel(channel) {
        const checkbox = document.querySelector('input[type="checkbox"]#' + channel);
        const status = checkbox.checked;
        const idPrefix = 'lead_contact_frequency_rules_lead_channels_';
        const inputs = ['frequency_number_', 'frequency_time_', 'contact_pause_start_date_', 'contact_pause_end_date_'];
        for (let i = 0; i < inputs.length; i++) {
            var element = document.getElementById(idPrefix + inputs[i] + channel);
            if (element) {
                element.disabled = !status;
            }
        }
    }

    function saveUnsubscribePreferences(formId) {
        var forms = document.getElementsByName(formId);
        for (var i = 0; i < forms.length; i++) {
            if (forms[i].tagName === 'FORM') {
                forms[i].submit();
            }
        }
    }
}
