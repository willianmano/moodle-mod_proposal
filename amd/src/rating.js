/**
 * Convert boostrap alert to sweetalert.
 *
 * @package
 * @subpackage    mod_evokeportfolio
 * @copyright  2021 World Bank Group <https://worldbank.org>
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 */

import {add as addToast} from 'core/toast';
import Ajax from 'core/ajax';

export const init = () => {
    document.querySelectorAll('.star-rating:not(.readonly) label').forEach(star => {
        star.addEventListener('click', function(event) {
            this.style.transform = 'scale(1.2)';

            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 200);

            sendRating(event.target.dataset.entryid, event.target.dataset.rate);
        });
    });
};

const sendRating = (entryid, rate) => {
    var request = Ajax.call([{
        methodname: 'mod_proposal_rate',
        args: {
            entryid: entryid,
            rate: rate
        }
    }]);

    request[0].done(function(response) {
        const elm = document.getElementById('average-badge-div-' + entryid);
        elm.innerHTML = response.rate;
        addToast('Avaliação enviada', {type: 'success'});
    }.bind(this)).fail(function(error) {
        window.console.log(error);
        var message = error.message;

        if (!message) {
            message = error.error;
        }

        addToast(message, {type: 'danger'});
    });
};