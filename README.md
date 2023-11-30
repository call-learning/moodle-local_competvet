Moodle Local CompetVet
==
[![CI Tests (Testing)](https://github.com/call-learning/moodle-local_competvet/actions/workflows/ci.yml/badge.svg)](https://github.com/call-learning/moodle-local_cveteval/actions/workflows/ci.yml)


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
            "url":"http:\/\/SITEURL\/local\/cveteval\/login\/cas-login.php?authCAS=CAS",
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
export SITEDOMAIN=<Your SITE DOMAIN>
export USERID=<Your user ID> (optional)
```
### Get the application mode: local_competvet_get_application mode

This API callback is used to get the application mode (either in student or observer mode). It is used by the mobile application to see which mode it should be in once the
user logs in.
If mode is "unknown", then the user is not allowed to use the application (an error message should be displayed to the user).

```bash
 curl https://$SITEDOMAIN/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_application_mode' \
  -d "userid=$USERID" \
  -d 'moodlewsrestformat=json' -k
```

You can omit the userid if you are just looking for the current logged in user:

```bash
 curl https://$SITEDOMAIN/webservice/rest/server.php \
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
 curl https://$SITEDOMAIN/webservice/rest/server.php \
  -d "wstoken=$TOKEN" \
  -d 'wsfunction=local_competvet_get_user_profile' \
  -d "userid=$USERID" \
  -d 'moodlewsrestformat=json' -k
```

The typical answer is:

```json
{
  "userid":3,
  "fullname":"LNAME FNAME",
  "firstname":"FNAME",
  "lastname":"LNAME",
  "username":"fname.lname",
  "userpictureurl":"http:\/\/SITEURL\/theme\/image.php\/boost\/core\/1699374164\/u\/f1"
}
```

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
