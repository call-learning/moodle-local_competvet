Moodle Local CompetVet
==
[![CI Tests (Testing)](https://github.com/call-learning/moodle-local_competvet/actions/workflows/ci.yml/badge.svg)](https://github.com/call-learning/moodle-local_competvet/actions/workflows/ci.yml)


Next iteration for CompetVetEval.

This project contains all services and mobile application for CompetVetEval.

## Using the API

First steps with curl.

### The local_competvet_get_idplist

This API callback is a bit different from the others. It is used to get the list of currently
setup idp (like auth_cas). It is used by the mobile application to get the list of CAS servers.
It is the only one that will be used without login so that's why we use the /lib/ajax/service-nologin.php URL.
Bear in mind that even with this, we cannot have any empty arguments (like the args parameter cannot
be empty). So we will need to explicitely set it to an empty array.

The payload is the following (json):
[{"methodname":"local_competvet_get_idplist","args":[]}]

```bash
 curl https://<Your URL>/lib/ajax/service-nologin.php \
  -d 'args=%5B%7B%22methodname%22%3A%22local_competvet_get_idplist%22%2C%22args%22%3A%5B%5D%7D%5D'
```
This should return an empty array, or an array of idp if any set.

If set, this is what the result looks like:
 * An url toward the ICON if any defined
 * Name of the IDP
 * URL for the IDP

```json
[
   {
      "error":false,
      "data":[
         {
            "url":"http:\/\/SITEURL\/local\/competvet\/login\/cas-login.php?authCAS=CAS",
            "name":"CAS",
            "iconurl":""
         }
      ]
   }
]
```

### Authentication to the web services

For the rest of the API, we will need to authenticate. We will use the same method as the mobile here so it can be translated easily
to the mobile application.
More documentation here: https://docs.moodle.org/dev/Creating_a_web_service_client

We will use a customised token.php file so we can actually get more errors (the original version located in /login/token.php will only
return a generic error message).

The name of the service is competvet_app_service.

```bash
 curl https://<Your URL>/local/competvet/webservices/token.php \
  -d 'username=<Your username>' \
  -d 'password=<Your password>' \
  -d 'service=competvet_app_service'
```
The usual return value for this is something like:
* token that can be used in the next query
* privatetoken is only for https, cna be ignored for now.

```json
{
  "token":"abcefghijklmnopqrstuvwxyz",
  "privatetoken":"abcdefghijklmnopqrstuvwxyz",
  "userid":"3"
}
```
In the following section we will use TOKEN as the token value.

Best is probably to define the several bash variables:

```bash
export TOKEN=<Your token>
export SITEURL=<Your SITE BASE URL>
export USERID=<Your user ID> (optional)
```
### Get the application mode: local_competvet_get_application mode

This API callback is used to get the application mode (either in student or observer mode). It is used by the mobile application to see which mode it should be in once the
user logs in.
If mode is "unknown", then the user is not allowed to use the application (an error message should be displayed to the user).

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_application_mode' \
  -d "userid=$USERID" \
  -d 'moodlewsrestformat=json' -k
```

You can omit the userid if you are just looking for the current logged in user:

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_application_mode' \
  -d 'moodlewsrestformat=json' -k
```

### Get the user profiles: local_competvet_get_user_profile

This API callback is used to get the list of user profiles. It is used by the mobile application to get the list of user 
profile information (like userid, fullname...).
Note that we add the moodlerestformat=json so the anwer is in json format.


The way it is called:
```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_user_profile' \
  -d "userid=$USERID" \
  -d 'moodlewsrestformat=json' -k
```

The typical answer is:

```json lines
{
  "userid":3,
  "fullname":"LNAME FNAME",
  "firstname":"FNAME",
  "lastname":"LNAME",
  "username":"fname.lname",
  "userpictureurl":"http:\/\/SITEURL\/theme\/image.php\/boost\/core\/1699374164\/u\/f1"
}
```
### Get the situation for current user: local_competvet_get_situations

This api callback is used to get the list of situations for the current user. It is used by the mobile application to get the list of situations
for the current user.

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_situations' \
  -d 'moodlewsrestformat=json' -k
```

#### Sample:

```json lines
[
    {
        "id": 5,
        "shortname": "ANE-2",
        "name": "UC0514 Anesth\u00e9sie 2",
        "intro": "<div class=\"text_to_html\">Test competvet 3</div>",
        "evalnum": 1,
        "autoevalnum": 1,
        "roles": "[\"student\"]",
        "tags": "[\"y:3\"]",
        "plannings": [
            {
                "id": 56,
                "startdate": 1704693600,
                "enddate": 1705298400,
                "groupname": "Group 4",
                "groupid": 22,
                "session": "2023",
                "situationid": 5
            },
            {
                "id": 57,
                "startdate": 1705298400,
                "enddate": 1705903200,
                "groupname": "Group 4",
                "groupid": 22,
                "session": "2023",
                "situationid": 5
            }
        ]
    },
    {
        "id": 4,
        "shortname": "MSP-1",
        "name": "UC0512 Cardio/Ophtalmo 1",
        "intro": "<div class=\"text_to_html\">Test competvet 2</div>",
        "evalnum": 1,
        "autoevalnum": 1,
        "roles": "[\"student\"]",
        "tags": "[\"y:2\"]",
        "plannings": [
            {
                "id": 44,
                "startdate": 1711951200,
                "enddate": 1712556000,
                "groupname": "Group 4",
                "groupid": 22,
                "session": "2023",
                "situationid": 4
            }
        ]
    },
    {
        "id": 3,
        "shortname": "REP-0",
        "name": "UC0513 Repro 0",
        "intro": "<div class=\"text_to_html\">Test competvet 1</div>",
        "evalnum": 1,
        "autoevalnum": 1,
        "roles": "[\"student\"]",
        "tags": "[\"y:1\"]",
        "plannings": [
            {
                "id": 6,
                "startdate": 1703484000,
                "enddate": 1704088800,
                "groupname": "Group 4",
                "groupid": 22,
                "session": "2023",
                "situationid": 3
            },
        ]
    }
]

```
### Get the plannings information local_competvet_get_plannings_info

This api callback relies on values retrieved by local_competvet_get_situations (especially the ID of the planning). 
The values returned by this API are the statistics regarding the plannings and will help to sort the planning by categories (like current, late...).
This will allow to display the plannings in the mobile application by categories.

Normally you provide all the planning id from a given situation (retrieved by local_competvet_get_situations) and you get the statistics for them. This
can be used to retrieve information for one planning at a time.


```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_plannings_info' \
  -d "plannings=[5143,5158]" \
  -d 'moodlewsrestformat=json' -k
```
The result (here for two plannings)
```json
[
  {
    "id": 5143,
    "category": 10,
    "categorytext": "Late",
    "groupstats": {
      "groupid": 500,
      "nbstudents": 20
    },
    "info": {
      "planningid": 5143,
      "startdate": 1703484000,
      "enddate": 1704088800,
      "groupname": "Group 4",
      "groupid": 22,
      "session": "2023",
      "situationid": 3,
      "situationname": "UC0513 Repro 0",
    }
  },
  {
    "id": 5158,
    "category": 10,
    "categorytext": "Late",
    "groupstats": {
      "groupid": 500,
      "nbstudents": 20
    },
    "info": {
        "planningid": 5158,
        "startdate": 1703484000,
        "enddate": 1704088800,
        "groupname": "Group 4",
        "groupid": 22,
        "session": "2023",
        "situationid": 3
    }
  }
]
```

### Get the plannings information local_competvet_get_plannings_info

This api callback relies on values retrieved by local_competvet_get_situations (especially the ID of the planning).
The values returned by this API are the statistics regarding the plannings and will help to sort the planning by categories (like current, late...).
This will allow to display the plannings in the mobile application by categories.

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_planning_info' \
  -d "planningid=5158" \
  -d 'moodlewsrestformat=json' -k
```

Results:
```json
{
  "id": 8008,
  "startdate": 1709506800,
  "enddate": 1710111600,
  "groupname": "Group 0",
  "groupid": 560,
  "session": "2023",
  "situationid": 71,
  "situationname": "UC0514 Anesth\u00e9sie 2",
}
```

### Get the user information for plannings: local_competvet_get_users_infos_for_planning

This API callback is used to get the list of users for a given planning. It is used by the mobile application to get the list of users 
categorised by role (student, observer...).

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_users_infos_for_planning' \
  -d "planningid=8008" \
  -d 'moodlewsrestformat=json' -k
```

Get a list of user for a given planning. The result is a list of users with their role (student, observer...) but also for students the
statistics for their evaluation. The role is a string that can be used to determine the exact role (not used currently in the app, except maybe for display).

```json
{
  "students": [
    {
      "userinfo": {
        "id": 932,
        "fullname": "Jakub Černý",
        "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704452514/u/f1"
      },
      "planninginfo": [
        {
          "type": "eval",
          "nbdone": 0,
          "nbrequired": 4
        },
        {
          "type": "autoeval",
          "nbdone": 0,
          "nbrequired": 2
        }
      ]
    },
    {
      "userinfo": {
        "id": 887,
        "fullname": "Timm Fischer",
        "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704452514/u/f1"
      },
      "planninginfo": [
        {
          "type": "eval",
          "nbdone": 0,
          "nbrequired": 4
        },
        {
          "type": "autoeval",
          "nbdone": 0,
          "nbrequired": 2
        }
      ]
    }
  ],
  "observers": [
    {
      "userinfo": {
        "id": 965,
        "fullname": "芳 李",
        "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704452514/u/f1",
        "role": "responsibleucue"
      }
    },
    {
      "userinfo": {
        "id": 860,
        "fullname": "Lukáš Černý",
        "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704452514/u/f1",
        "rolename": "evaluator"
      }
    }
  ]
}

```


### Get observation information (Eval component)

This API allows to retrieve information about the observations for a given user. It is used by the mobile application to display the list of observations
for a given user.

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_user_eval_observations' \
  -d "userid=$USERID" \
  -d "planningid=8008" \
  -d 'moodlewsrestformat=json' -k
```

The result is a set of evaluation and evaluaiton time. The evaluation time is the time at which the evaluation was done. The evaluation is the status of the evaluation (1: not done, 2: done, 3: done and validated).
Category is the category of the evaluation (1: autoevaluation, 2: observation).

```json
[
  {
    "id": 79782,
    "studentid": 932,
    "observerid": 971,
    "status": 1,
    "time": 1703775582,
    "category": 2,
    "categorytext": "Observations"
  },
  {
    "id": 79786,
    "studentid": 932,
    "observerid": 932,
    "status": 1,
    "time": 1703775582,
    "category": 1,
    "categorytext": "Autoevaluations"
  },
  {
    "id": 79787,
    "studentid": 932,
    "observerid": 932,
    "status": 2,
    "time": 1703775582,
    "category": 1,
    "categorytext": "Autoevaluations"
  }
]
```
### Get observation information (Eval component)

This API allows to retrieve information about a given observation. It is used by the mobile application to display the content of the obserrvation.

```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_eval_observation_info' \
  -d "observationid=79787" \
  -d 'moodlewsrestformat=json' -k
```

Returned value is:

```json
{
  "id": 79787,
  "category": 2,
  "context": {
    "id": 511800,
    "userinfo": {
      "id": 971,
      "fullname": "秀英 黃",
      "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704462827/u/f1"
    },
    "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id</p>",
    "timecreated": 1703776479,
    "timemodified": 1703776479
  },
  "comments": [
    {
      "id": 511801,
      "type": 2,
      "userinfo": {
        "id": 971,
        "fullname": "秀英 黃",
        "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704462827/u/f1"
      },
      "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id</p>",
      "commentlabel": "",
      "timecreated": 1703776479,
      "timemodified": 1703776479
    },
    {
      "id": 511802,
      "type": 3,
      "userinfo": {
        "id": 971,
        "fullname": "秀英 黃",
        "userpictureurl": "http://competveteval.local/theme/image.php/boost/core/1704462827/u/f1"
      },
      "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id</p>",
      "commentlabel": "",
      "timecreated": 1703776479,
      "timemodified": 1703776479
    },
  ],
  "criteria": [
    {
      "criterioninfo": {
        "id": 1,
        "label": "Savoir être",
        "idnumber": "Q001",
        "sort": 1,
        "parentid": 0
      },
      "level": 50,
      "subcriteria": [
        {
          "criterioninfo": {
            "id": 2,
            "label": "Respect des horaires de travail",
            "idnumber": "Q002",
            "sort": 1,
            "parentid": 1
          },
          "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id"
        },
        {
          "criterioninfo": {
            "id": 3,
            "label": "Respect des interlocuteurs (clients, personnels, encadrants, pairs, ...)",
            "idnumber": "Q003",
            "sort": 2,
            "parentid": 1
          },
          "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id"
        },
        {
          "criterioninfo": {
            "id": 4,
            "label": "Respect du bien-être des animaux",
            "idnumber": "Q004",
            "sort": 3,
            "parentid": 1
          },
          "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id"
        },
        {
          "criterioninfo": {
            "id": 5,
            "label": "Respect des consignes vestimentaires, d’hygiène et de de biosécurité",
            "idnumber": "Q005",
            "sort": 4,
            "parentid": 1
          },
          "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id"
        },
        {
          "criterioninfo": {
            "id": 6,
            "label": "Respect du matériel mis à disposition",
            "idnumber": "Q006",
            "sort": 5,
            "parentid": 1
          },
          "comment": "<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id"
        }
      ]
    }
  ]
}


```
### Get situation criteria (Eval component)

This API allows to retrieve information about the criteria for a given situation. It is used by the mobile application to display the list of criteria


```bash
 curl $SITEURL/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_situation_criteria' \
  -d "situationid=69" \
  -d 'moodlewsrestformat=json' -k
```

Returns the list of all criteria for the given situation:

```json
[
  {
    "id": 28,
    "label": "Capacité à identifier un motif de consultation (animal ou groupe d’animaux) et à recueillir une anamnèse",
    "idnumber": "Q028",
    "sort": 1,
    "parentid": 27
  },
  {
    "id": 17,
    "label": "Capacité à mobiliser les notions théoriques en situation clinique",
    "idnumber": "Q017",
    "sort": 1,
    "parentid": 16
  },
  {
    "id": 2,
    "label": "Respect des horaires de travail",
    "idnumber": "Q002",
    "sort": 1,
    "parentid": 1
  }
 
]
```

### Get Evaluation Observation Information: `local_competvet_get_eval_observation_info`

The `local_competvet_get_eval_observation_info` API function retrieves detailed information about a specific evaluation observation. This includes data about the observation context, comments made, and various evaluation criteria.

#### Parameters:

- `observationid`: (integer) The ID of the evaluation observation to retrieve information for.

**Usage Example with curl:**

```bash
curl $SITEURL/webservice/rest/server.php    
 -d "wstoken=$TOKEN"  
  -d 'wsfunction=local_competvet_get_eval_observation_info'  
   -d 'observationid=[observation_id]'   -d 'moodlewsrestformat=json' -k
```

Replace `[observation_id]` with the actual ID of the observation you want to retrieve information for.

#### Expected Output:

A JSON object containing detailed information about the evaluation observation, including its context, comments, criteria, and whether it can be edited or deleted.

**Sample Response:**

```json
{
    "id": 88214,
    "category": 2,
    "context": {
        "id": 528322,
        "comment": "test",
        "userinfo": {
            "id": 853,
            "fullname": "Anastasia Kozlova",
            "userpictureurl": "http://competveteval.local/theme/image.php/_s/boost/core/1706289695/u/f1"
        },
        "timecreated": 1706303774,
        "timemodified": 1706303774
    },
    "comments": [
        {
            "id": 528323,
            "comment": "test comment",
            "commentlabel": "",
            "type": 2,
            "userinfo": {
                "id": 853,
                "fullname": "Anastasia Kozlova",
                "userpictureurl": "http://competveteval.local/theme/image.php/_s/boost/core/1706289695/u/f1"
            },
            "timecreated": 1706303774,
            "timemodified": 1706303774
        }
    ],
    "criteria": [
        // ... Criteria details ...
    ],
    "canedit": false,
    "candelete": false
}
```

The response includes an array of `criteria` each containing detailed information about specific evaluation criteria, as well as permissions for editing and deleting the observation.


### Create Evaluation Observation: `local_competvet_create_eval_observation`

This API function is used to create a new evaluation observation.
It allows the creation of a detailed observation record linked to a specific student, planning, and observer.

Parameters:
* planningid: (integer) The ID of the planning associated with this observation.
* studentid: (integer) The ID of the student who is the subject of the observation.
* observerid: (integer) The ID of the observer making the observation.
* context: (string) A brief description or context of the observation.
* comments: (array) An array of comments related to the observation.


**Usage Example:**

```bash
curl $SITEURL/webservice/rest/server.php  
    -d "wstoken=$TOKEN"   
    -d 'wsfunction=local_competvet_create_eval_observation'  
    -d 'planningid=12345' \
    -d 'studentid=67890' \
    -d 'observerid=13579' \
    -d 'context=Initial observation of student performance' \
    -d 'moodlewsrestformat=json' -k
```

Result:

```json
{
    "observationid": 88214
}
```
---

### Edit Evaluation Observation: `local_competvet_edit_eval_observation`

The `local_competvet_edit_eval_observation` API function allows for modifying an existing evaluation observation in the Moodle CompetVet system. This function enables updating the observation context and the criteria associated with the observation.

#### Parameters:

- `observationid`: (integer) The ID of the evaluation observation to be edited.
- `context`: (array) A context object containing:
    - `id`: (integer) The ID of the context.
    - `comment`: (string) A comment related to the context.
- `criteria`: (array) An array of criteria objects, each containing:
    - `id`: (integer) The ID of a criterion.
    - `level`: (integer) The level or score assigned to the criterion.

**Usage Example with curl:**

```bash
curl https://<Your URL>/webservice/rest/server.php   -d "wstoken=$TOKEN"   -d 'wsfunction=local_competvet_edit_eval_observation' \ 
  -d 'observationid=[observation_id]'   -d 'context[comment]=Test comment'   \
  -d 'criteria[0][id]=[criteria_id]&criteria[0][level]=100'   -d 'moodlewsrestformat=json' -k
```

Replace `[observation_id]`, `[context_id]`, and `[criteria_id]` with the actual IDs and values relevant to the observation you wish to edit.

There are different version of the payload that will just edit one type of item, like comment or criteria.

For example to edit a private comment in an observation:

```bash
curl https://<Your URL>/webservice/rest/server.php   -d "wstoken=$TOKEN"   -d 'wsfunction=local_competvet_edit_eval_observation' \ 
  -d 'observationid=[observation_id]'   -d 'comment[comment]=Test comment' -d 'comment[type]=4'   \
  -d 'moodlewsrestformat=json' -k
````

For eval:
* OBSERVATION_COMMENT = 2;
* OBSERVATION_PRIVATE_COMMENT = 4;

For autoeval:
* AUTOEVAL_PROGRESS = 10;
* AUTOEVAL_AMELIORATION = 11;
* AUTOEVAL_MANQUE = 12;
* AUTOEVAL_OBSERVER_COMMENT = 13;
---

### Delete Evaluation Observation: `local_competvet_delete_eval_observation`

This API function is used to delete an evaluation observation.

**Usage Example:**

```bash
curl $SITEURL/webservice/rest/server.php   
  -d "wstoken=$TOKEN"   
  -d 'wsfunction=local_competvet_delete_eval_observation'   -d 'id=[observationid]'   -d 'moodlewsrestformat=json' -k
```

---

### Ask for Evaluation Observation: `local_competvet_ask_eval_observation`

This API function is used to request an evaluation observation. This means that this will be added (with the context provided)
to the list of TODOs for the observer. The observer will then be able to create the observation.

**Usage Example:**

```bash
curl $SITEURL/webservice/rest/server.php   
  -d "wstoken=$TOKEN"   -d 'wsfunction=local_competvet_ask_eval_observation'  
  -d 'planningid=1234'
  -d 'studentid=345'
  -d 'observerid=345'
  -d 'context="Test string"'
  -d 'moodlewsrestformat=json' -k
```

Results:

```json
{
    "todoid": 88214
}
```
---

### Get Todos: `local_competvet_get_todos`

This API function is used to retrieve a list of todos.

**Usage Example:**

```bash
curl $SITEURL/webservice/rest/server.php   -d "wstoken=$TOKEN"   -d 'wsfunction=local_competvet_get_todos'   -d 'moodlewsrestformat=json' -k
```

---

### Update Todo Status: `local_competvet_update_todo_status`

This API function is used to update the status of a todo.

**Usage Example:**

```bash
curl $SITEURL/webservice/rest/server.php   -d "wstoken=$TOKEN"   -d 'wsfunction=local_competvet_update_todo_status'   -d 'additional_parameters_here'   -d 'moodlewsrestformat=json' -k
```

---

Please replace `<Your URL>`, `$TOKEN`, and `additional_parameters_here` with the appropriate values for your Moodle instance and the specific API calls.

## CAS and Tests ###

To test with CAS login (for testing only) you can follow the README.cas.md file.

## License ##

2023 CALL Learning <laurent@call-learning.fr>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.

# Upgrade notes (API)

### 2024-01-26

Added: 

* local_competvet_get_planning_info : get planning info, add the situation name so it is easier to display.
* local_competvet_get_planning_infos_students : get planning info for all students in a given planning. For observer add the role as role in the userinfo.

New API:
* local_competvet_create_eval_observation
* local_competvet_edit_eval_observation
* local_competvet_delete_eval_observation
* local_competvet_ask_eval_observation
* local_competvet_get_todos
* local_competvet_update_todo_status
### 2024-01-05

Removed: 
* get_users_for_planning : no longer useful and replaced by local_competvet_get_users_infos_for_planning
* local_competvet_get_planning_infos_students : changed into local_competvet_get_planning_infos_student (singular) to get planning
info for one student only.

get_user_info : will return id instead of userid.


### 2023-12-29
Renamed local_competvet_get_user_evaluations => get_user_eval_observations so each component will have its own endpoint. 

