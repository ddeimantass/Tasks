## Composite Design Pattern Task

### Requirements

- Create an application using PHP and any framework;
- The application should have simple UI and required API endpoints;
- Create a readme file with instructions on how to run the application;
- If while creating or updating task "is_done" field is set to "true" then subtasks should be set also as done;
- If "is_done" field is set to "false" then all parent tasks should be set also as not done;
- If a task has subtasks then task points field value becomes a sum of subtasks points;
- Follow PSR-2 style guide;
- Should be written in OOP;
- Use design patterns when needed;
- Make sure the code in simple and clean;
- Separate concerns;
- Testable code.

### Build and Run

- pull this repository into your local server directory
```
composer install
```
- change DB credentials in .env
```
bin/console d:d:c
bin/console d:m:m -n
symfony serve 
```
- create some tasks using API 
- open this repository in browser

### Run Tests

```
bin/console d:d:c --env=test
bin/console d:s:u --force --env=test
bin/phpunit 
```
### API

#### Create Endpoint

Method: POST
URI: /api/task
Success response code: 201 
Request example:
```
{
    "parent_id":1,
    "user_id":1,
    "title":"Task 1",
    "points":3,
    "is_done":0,
    "email":"john.doe@email.com"
}
```
Response example:
```
{
    "id":1,
    "parent_id":1,
    "user_id":1,
    "title":"Task 1",
    "points":3,
    "is_done":0,
    "created_at":"2020-01-01 00:00:00",
    "updated_at":"2020-01-01 00:00:00"
}
```
Validations:
- parent_id: existing task id or null;
- user_id: required and existing user id;
- title: required;
- point: required, integer where the minimum value is 1 and the maximum value is 10;
- is_done: required, integer, 0 or 1;

#### Update Endpoint

Method: PUT
URI: /api/task/{task_id}
Success response code: 201 
Request example:
```
{
    "parent_id":1,
    "user_id":1,
    "title":"Task 1",
    "points":10,
    "is_done":1,
    "email":"john.doe@email.com"
}
```
Response example:
```
{
    "id":1,
    "parent_id":1,
    "user_id":1,
    "title":"Task 1",
    "points":3,
    "is_done":0,
    "created_at":"2020-01-01 00:00:00",
    "updated_at":"2020-01-01 00:00:00"
}
```
Validations:
- parent_id: existing task id or null;
- user_id: required and existing user id;
- title: required;
- points: required, integer where the minimum value is 1 and the maximum value is 10;
- is_done: required, integer, 0 or 1;

### TO DOs
- implement DELETE endpoint
- Improve UI
- Increase test coverage
- Add some fixtures
- Remove lchrusciel/api-test-case package deprecations
