// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript adding a new observation.
 *
 * @module     local_competvet/local/forms/observation_add_form
 * @copyright  2023 Laurent David <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';
import Notification from 'core/notification';
import Ajax from 'core/ajax';
import {createModalDebug} from "./modal_debug";
import {init as modInit} from "mod_competvet/local/forms/eval_observation_ask";
import {createModalDebugFromEvent} from "./modal_debug";

export const init = (modulename) => {
    modInit(modulename, createModalDebugFromEvent);
};

export const initUsersActionMobileView = (modulename, planningId, studentId, context) => {
    const selectedElements = document.querySelectorAll('.ask-observation-modal [data-user-id]');
    if (!selectedElements) {
        return;
    }
    selectedElements.forEach((element) => {
        element.addEventListener('click', async (event) => {
            event.preventDefault();
            const askEvalPayload = {
                context: context,
                planningid: planningId,
                observerid: element.dataset.userId,
                studentid: studentId,
            };
            try {
                const askObservationReturn = await Ajax.call(
                    [{methodname: `local_competvet_ask_eval_observation`, args: askEvalPayload}]
                )[0];
                if (askObservationReturn.todoid) {
                    try {
                        const userInfo = await Ajax.call([
                            {
                                methodname: `mod_competvet_get_user_profile`,
                                args: {
                                    userid: element.dataset.userId,
                                },
                            }
                        ])[0];
                        const usernameString = await getString('observation:asked:body', 'mod_competvet', userInfo.fullname);
                        await createModalDebug({
                            content: usernameString,
                            debugs: [
                                {
                                    apifunction: 'local_competvet_ask_eval_observation',
                                    params: JSON.stringify(askEvalPayload),
                                    results: [
                                        JSON.stringify(askObservationReturn)
                                    ]
                                }
                            ]
                        });
                        element.classList.add('text-success');
                    } catch (error) {
                        await Notification.exception(error);
                    }
                }
            } catch (error) {
                const cannotAddString = await getString('todo:cannotadd', 'mod_competvet');
                await Notification.exception({message: cannotAddString});
            }
        });
    });
};