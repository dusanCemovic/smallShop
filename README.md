# CREATIM PHP Shop
PHP application implementing:
- Articles
- Subscription packages
- Orders with multiple articles + one subscription
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
    - allowDeleting - each model has overwritten method "allowDeleting" that control deleting
    - customThingsToBeDone - each model can overwrite method "customThingsToBeDone" to do something extra after deleting info
- CONTROLLER (APP/Controller): BaseController is abstract controller which has only method which some controller should have like render
  - Each controller has default render and abstract method "index"
  - Important are methods
    - render - is default method which can be overwritten 
    - logError - which get all error thrown from exceptions so we may handle globally
  - Main Controller method is place older which connect everything
- VIEW (APP/Views): main view element is layout.php with header, and each view is loaded inside that one
- DB (migration/db.sql):
  - In task, was given that each person is unique by phone number, but i put in separate table so we can delete person (delete phone number) but leave as id if it has history in order, soft delete.
  - each table has deleted_at, to allow this soft delete
- ROUTER (index.php): It is done in /index.php file. We allow autoload like in any new framework or environment
  - Due to the simplicity, every route is via _GET param and then parsed
- OTHER:
  - Config file with credentials and other info is added in config file. In practice, we can also use env file.


