# Small PHP Shop
PHP application implementing:
- Articles
- Subscription packages
- Orders with multiple articles + one subscription. Has to be unique articles by customer and only one subscription.
- SMS sending with two pseudocode providers which are checked via DB for limit (todo)

## Setup
1. Create MySQL DB using `migrations/db.sql`.
2. Update `config/config.php` with your DB credentials.
3. Serve with PHP built-in server:
   ```
   php -S localhost:8000 -t public
   ```
4. Open `http://localhost:8000`

## Notes
- Rules:
    - Customer unique by phone
    - Each customer can have at most one subscription
    - Each customer can buy at most one unique article across orders
    - Customer can have more orders but previous things need to be fulfilled
- Deleting purchased items, subscriptions and users uses soft delete.


## Descriptions
- MODEL (APP/Models): BaseModel is abstract model which has only basic methods as getting all or find one
  - Important are methods
    - delete - which controller if it is allowed to delete data from database or just to put to soft delete
    - allowDeleting - each model has the overwritten method "allowDeleting" that control deleting
    - customThingsToBeDone - each model can overwrite the method "customThingsToBeDone" to do something extra after deleting info
- CONTROLLER (APP/Controller): BaseController is the abstract controller that has only method which some controller should have like render
  - Each controller has the default render and abstract method "index"
  - Important are methods
    - render - is default method which can be overwritten 
    - logError - which get all error thrown from exceptions so we may handle globally
  - Main Controller method is OrdersController which connects everything
- VIEW (APP/Views): main view element is layout.php with header, and each view is loaded inside that one
- DB (migration/db.sql):
  - In task, was given that each person is unique by phone number, but i put in separate table so we can delete person (delete phone number) but leave as id if it has history in order, soft delete.
  - each table has deleted_at, to allow this soft delete
- ROUTER (index.php): It is done in /index.php file. We allow autoload like in any new framework or environment
  - Due to the simplicity, every route is via _GET param and then parsed
- SMS - SERVICES (ServicesSMS)
  - Everything is controlled by Manager. Interface is used for both providers which has only pseudocode
  - THIS CAN BE OPTIMIZED using redis or some other server addons to avoid calling db for checking how many sms are sent in last 1 minutes
  - Also, writing each log in the database can be done via cronjob to also avoid writing in db for each sms
- OTHER:
  - Config file with credentials and other info is added in the config file. In practice, we can also use the env file.
  - Even that i added constraint for tables, some of those will not be done cuz we have custom checking to call for soft delete
  - Exception triggers can be organized differently that some of those goes to the log file, and some are going to customer direct on front-end.

## Posible improvements
- We can optimize checking sms limit with redis or some other server addons.
- In some new frameworks, we can use articles, subscriptions and orders as objects. If I am using Laravel, I will use it. Probably in with Symfony, something similar can be used.
- We can add tests for each important method.
- Some things like creating and editing (to avoid redundancy) can be put in one form, or even better, we can put in the base class which will be extended by each controller.
- We can create more methods in the base class to make it more generic.



