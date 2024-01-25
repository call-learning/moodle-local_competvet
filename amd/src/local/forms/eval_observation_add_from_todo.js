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
import Ajax from 'core/ajax';
import ModalEvents from 'core/modal_events';
import {genericFormCreate, getSelectedElement} from "mod_competvet/local/forms/generic_form_helper";
import {createModalDebug, createModalDebugFromEvent} from "./modal_debug";
import {get_string as getString} from 'core/str';
import Notification from 'core/notification';

export const init = async (modulename) => {
    const selectedElements = getSelectedElement('addfromtodo');
    if (!selectedElements) {
        return;
    }
    selectedElements.forEach((element) => {
        element.addEventListener('click', async (event) => {
            event.preventDefault();
            const data = event.target.closest('[data-action]').dataset;
            const payLoad = {
                category: 1, // Observation.
                planningid: data.planningId,
                studentid: data.studentId,
                observerid: data.observerId,
                context: data.context,
            };
            const observation = await Ajax.call([
                {
                    methodname: `local_competvet_create_eval_observation`,
                    args: payLoad,
                }
            ])[0];
            const modalObs = await createModalDebug({
                content: await getString('observation:created', 'local_competvet'),
                debugs: [
                    {
                        apifunction: 'local_competvet_create_eval_observation',
                        params: JSON.stringify(payLoad),
                        results: [
                            JSON.stringify(observation)
                        ]
                    }
                ]
            });
            modalObs.show();
            // Replace :OBSERVATIONID by the real observation id in the return url..
            const returnUrl = data.returnurl.replace('OBSERVATIONID', observation.observationid);
            modalObs.getRoot().on(ModalEvents.cancel, () => processTodo(
                data.todoId,
                {id: observation.observationid, returnurl: returnUrl},
                modulename
            ));
        });
    });
};

const processTodo = async (todoId, editObservationParameters, modulename) => {
    const payLoad = {
        id: todoId,
    };
    try {
        const updatedTodo = await Ajax.call([
            {
                methodname: `local_competvet_update_todo_status`,
                args: payLoad,
            }
        ])[0];
        const modalTodo = await createModalDebug({
            content: await getString('todo:updated', 'local_competvet'),
            debugs: [
                {
                    apifunction: 'local_competvet_update_todo',
                    params: JSON.stringify(payLoad),
                    results: [
                        JSON.stringify(updatedTodo)
                    ]
                }
            ]
        });
        modalTodo.show();
        modalTodo.getRoot().on(ModalEvents.cancel, () => {
            genericFormCreate(editObservationParameters, 'edit', modulename, createModalDebugFromEvent);
        });
    } catch (error) {
        await Notification.exception(error);
    }
};
